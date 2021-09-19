<?php

namespace Rialto\Purchasing\Invoice\Reader\Email\Web;

use Rialto\Email\MailerInterface;
use Rialto\Purchasing\Invoice\Orm\SupplierInvoicePatternRepository;
use Rialto\Purchasing\Invoice\Orm\SupplierInvoiceRepository;
use Rialto\Purchasing\Invoice\Parser\SupplierInvoiceParserException;
use Rialto\Purchasing\Invoice\Reader\Email\AttachmentParser;
use Rialto\Purchasing\Invoice\Reader\Email\ShortShipmentInvoiceEmail;
use Rialto\Purchasing\Invoice\Reader\Email\SupplierEmail;
use Rialto\Purchasing\Invoice\Reader\Email\SupplierMailbox;
use Rialto\Purchasing\Invoice\SupplierInvoice;
use Rialto\Purchasing\Invoice\SupplierInvoicePattern;
use Rialto\Purchasing\Invoice\Web\SupplierInvoiceParseType;
use Rialto\Security\Role\Role;
use Rialto\Util\Collection\ObjectSorter;
use Rialto\Web\Response\JsonResponse;
use Rialto\Web\RialtoController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Zend\Mail\Exception\ExceptionInterface as MailException;

/**
 * Controller for reading emails from suppliers, usually for the purpose
 * of importing invoices.
 */
class SupplierEmailController extends RialtoController
{
    /** @var SupplierInvoicePatternRepository */
    private $patternRepo;

    /** @var SupplierInvoiceRepository */
    private $invoiceRepo;

    /* @var AttachmentParser */
    private $attachmentParser;

    /**
     * @var SupplierMailbox
     */
    private $mailbox;

    /**
     * Initialize any additional properties that the controller needs.
     */
    protected function init(ContainerInterface $container)
    {
        $this->patternRepo = $this->dbm->getRepository(SupplierInvoicePattern::class);
        $this->invoiceRepo = $this->dbm->getRepository(SupplierInvoice::class);
        $this->attachmentParser = $this->get(AttachmentParser::class);
        $this->mailbox = $this->get(SupplierMailbox::class);
    }

    /**
     * List all emails in the inbox.
     *
     * @Route("/Purchasing/SupplierEmail", name="supplier_email_list")
     * @Template("purchasing/invoice/reader/email/list.html.twig")
     */
    public function listAction(Request $request)
    {
        $this->denyAccessUnlessGranted(Role::ADMIN);
        try {
            $emails = $this->mailbox->getAll();
        } catch (MailException $ex) {
            return $this->mailboxUnavailable($ex);
        }

        usort($emails, new ObjectSorter($request->get("_order", "date")));

        return [
            'emails' => $emails,
            'archivedFolder' => SupplierMailbox::FOLDER_ARCHIVE,
            'ignoredFolder' => SupplierMailbox::FOLDER_IGNORE,
        ];
    }

    private function mailboxUnavailable(MailException $ex)
    {
        $msg = "Mailbox unavailable: {$ex->getMessage()}";
        return new Response($msg, Response::HTTP_SERVICE_UNAVAILABLE);
    }

    /**
     * Parses the invoice file.
     *
     * @Route("/Purchasing/SupplierEmail/{messageId}",
     *   name="supplier_email_parse")
     * @Template("purchasing/invoice/reader/email/parse.html.twig")
     */
    public function parseAction(string $messageId, Request $request)
    {
        $this->denyAccessUnlessGranted(Role::ADMIN);
        try {
            if (!$this->mailbox->hasMessage($messageId)) {
                throw $this->notFound("No such message $messageId");
            }
            $email = $this->mailbox->getMessage($messageId);
        } catch (MailException $ex) {
            return $this->mailboxUnavailable($ex);
        }

        $templateParams = [
            'email' => $email,
            'forms' => [],
        ];
        $templateParams['folders'] = $this->mailbox->getFolders();
        $templateParams['defaultFolder'] = SupplierMailbox::FOLDER_ARCHIVE;

        $pattern = $this->findMatchingPattern($email);
        if (!$pattern) {
            $templateParams['error'] = 'No supplier matches this email';
            return $templateParams;
        }
        $email->setPattern($pattern);

        try {
            $this->attachmentParser->findInvoices($email);
        } catch (SupplierInvoiceParserException $ex) {
            $templateParams['error'] = $ex->getMessage();
            return $templateParams;
        }

        $forms = [];
        foreach ($email->getInvoices() as $invoice) {
            $existing = $this->findAlreadyEntered($invoice);
            if ($existing) {
                $email->addFinishedInvoice($existing);
            } else {
                $key = $invoice->getIndexKey();
                $options = ['order_required' => true];
                $forms[$key] = $this->createForm(SupplierInvoiceParseType::class, $invoice, $options);
            }
        }

        if ($request->isMethod('post')) {
            $key = $request->get('formToProcess');
            /* @var $form FormInterface */
            $form = $forms[$key];
            $form->handleRequest($request);
            if ($form->isValid()) {
                $this->dbm->beginTransaction();
                try {
                    /* @var $invoice SupplierInvoice */
                    $invoice = $form->getData();
                    assertion($invoice instanceof SupplierInvoice);
                    $invoice->prepare();
                    if ($this->findAlreadyEntered($invoice)) {
                        $this->dbm->rollBack();
                        return JsonResponse::fromErrors([
                            "$invoice has already been entered"
                        ]);
                    }
                    $this->dbm->persist($invoice);
                    $this->dbm->flushAndCommit();
                    $this->checkForNewSupplierInvoiceItem($invoice);
                } catch (\Exception $ex) {
                    $this->dbm->rollBack();
                    throw $ex;
                }

                if ($request->get('approveAndMove')) {
                    $response = $this->moveAction($messageId, $request);
                    return JsonResponse::javascriptRedirect($response->getTargetUrl());
                } else {
                    $uri = $this->getCurrentUri();
                    $target = 'bottomPane';
                    return JsonResponse::javascriptRedirect($uri, $target);
                }
            } else {
                return JsonResponse::fromInvalidForm($form);
            }
        }

        $templateParams['forms'] = array_map(function (FormInterface $form) {
            return $form->createView();
        }, $forms);
        $templateParams['formAction'] = $this->getCurrentUri();
        return $templateParams;
    }

    private function checkForNewSupplierInvoiceItem(SupplierInvoice $invoice)
    {
        foreach ($invoice->getItems() as $item) {
            if($item->getQtyOrdered() > 0 && $item->getQtyInvoiced()==0){
                $this->sendEmailNotifyNewOrder($invoice);
            }
        }
    }

    private function sendEmailNotifyNewOrder(SupplierInvoice $invoice)
    {
        $sender = $this->getCurrentUser();
        $email = new ShortShipmentInvoiceEmail($sender, $invoice);
        $email->loadSubscribers($this->manager());
        $mailer = $this->get(MailerInterface::class);
        $mailer->send($email);
    }

    /** @return SupplierInvoicePattern|null */
    private function findMatchingPattern(SupplierEmail $email)
    {
        return $this->patternRepo->findMatching($email);
    }

    private function findAlreadyEntered(SupplierInvoice $invoice)
    {
        return $this->invoiceRepo->findBySupplierReference(
            $invoice->getSupplier(),
            $invoice->getSupplierReference());
    }

    /**
     * Moves a message into another folder
     *
     * @Route("/Purchasing/SupplierEmail/{messageId}/move",
     *   name="supplier_email_move")
     * @Method("POST")
     */
    public function moveAction(string $messageId, Request $request)
    {
        $this->denyAccessUnlessGranted(Role::ADMIN);
        $targetFolder = $request->get('folder');
        if (!$targetFolder) {
            throw $this->badRequest("No target folder specified");
        }

        try {
            $this->mailbox->moveMessage($messageId, $targetFolder);
            $this->logNotice("Moved message $messageId to \"$targetFolder\" successfully.");
        } catch (MailException $ex) {
            $this->logException($ex);
        }

        return $this->redirect($this->generateUrl('supplier_email_list'));
    }

    /**
     * Return a count of the number of messages in the mailbox.
     *
     * @Route("/purchasing/count-supplier-email/", name="count_supplier_email")
     * @Method("GET")
     */
    public function countAction()
    {
        $this->denyAccessUnlessGranted(Role::ADMIN);
        try {
            $count = number_format(count($this->mailbox));
        } catch (MailException $ex) {
            $count = '!';
        } catch (\ErrorException $ex) {
            $count = '!';
        }
        $response = new Response($count);
        $response->setMaxAge(120); // TODO: cache that can be invalidated
        return $response;
    }
}
