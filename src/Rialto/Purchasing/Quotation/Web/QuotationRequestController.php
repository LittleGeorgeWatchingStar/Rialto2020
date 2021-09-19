<?php

namespace Rialto\Purchasing\Quotation\Web;

use Gumstix\Storage\FileStorage;
use Rialto\Cms\CmsEngine;
use Rialto\Database\Orm\EntityList;
use Rialto\Email\Attachment\AttachmentZipper;
use Rialto\Email\EmailException;
use Rialto\Email\MailerInterface;
use Rialto\Purchasing\Quotation\Email\RequestForQuote;
use Rialto\Purchasing\Quotation\QuotationRequest;
use Rialto\Purchasing\Quotation\QuotationRequestRepository;
use Rialto\Security\Role\Role;
use Rialto\Stock\Item\StockItem;
use Rialto\Web\RialtoController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Templating\EngineInterface;

/**
 * Controller for requesting quotes from suppliers.
 */
class QuotationRequestController extends RialtoController
{
    /** @var QuotationRequestRepository */
    private $repo;

    protected function init(ContainerInterface $container)
    {
        $this->repo = $this->getRepository(QuotationRequest::class);
    }

    /**
     * @Route("/purchasing/rfq/", name="rfq_list")
     * @Method("GET")
     * @Template("purchasing/quotation/request-list.html.twig")
     */
    public function listAction(Request $request)
    {
        $this->denyAccessUnlessGranted([Role::ENGINEER, Role::PURCHASING]);
        $form = $this->createForm(QuotationRequestListFilterType::class);
        $queryParams = $request->query->all();
        $queryParams['_order'] = $queryParams['_order'] ?? 'recent';
        $form->submit($queryParams);
        $list = new EntityList($this->repo, array_merge($queryParams, $form->getData()));

        return [
            'form' => $form->createView(),
            'list' => $list,
        ];
    }

    /**
     * @Route("/purchasing/rfq/{id}/", name="rfq_view")
     * @Method("GET")
     * @Template("purchasing/quotation/request-view.html.twig")
     */
    public function viewAction(QuotationRequest $rfq)
    {
        $this->denyAccessUnlessGranted([Role::ENGINEER, Role::PURCHASING]);
        return ['entity' => $rfq];
    }

    /**
     * @Route("/purchasing/rfq/{stockCode}/version/{version}/", name="rfq_create")
     * @Template("purchasing/quotation/request-create.html.twig")
     */
    public function createAction(StockItem $item, $version, Request $request)
    {
        $this->denyAccessUnlessGranted([Role::ENGINEER, Role::PURCHASING]);
        $version = $item->getVersion($version);
        $model = new ManualQuotationRequest($this->getCurrentUser(), $version);
        $form = $this->createForm(ManualQuotationRequestType::class, $model);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $rfqList = $model->createRequests();
            $this->dbm->beginTransaction();
            try {
                foreach ($rfqList as $rfq) {
                    $this->dbm->persist($rfq);
                }
                $this->dbm->flushAndCommit();
            } catch (\Exception $ex) {
                $this->dbm->rollBack();
                throw $ex;
            }
            $this->logNotice("Quotation requests created successfully.");

            if ($request->get('sendNow')) {
                $this->dbm->beginTransaction();
                try {
                    $this->sendEmails($model, $rfqList);
                    $this->dbm->flushAndCommit();
                } catch (EmailException $ex) {
                    $this->dbm->rollBack();
                    $this->logException($ex);
                } catch (\Exception $ex) {
                    $this->dbm->rollBack();
                    throw $ex;
                }
            }

            $ids = array_map(function (QuotationRequest $rfq) {
                return $rfq->getId();
            }, $rfqList);
            return $this->redirectToRoute('rfq_list', ['ids' => $ids]);
        }

        return [
            'form' => $form->createView(),
            'item' => $item,
            'version' => $version,
        ];
    }

    private function sendEmails(ManualQuotationRequest $model, array $rfqList)
    {
        $emails = $model->createEmails($rfqList, $this->getDefaultCompany());
        foreach ($emails as $email) {
            $this->prepEmail($email);
            $this->sendEmail($email);
        }
        $this->logNotice("Emails sent successfully.");
    }

    private function prepEmail(RequestForQuote $email)
    {
        /** @var $cms EngineInterface */
        $cms = $this->get(CmsEngine::class);
        $email->render($cms);
        /** @var $storage FileStorage */
        $storage = $this->get(FileStorage::class);
        $email->loadAttachments($storage);
    }

    private function sendEmail(RequestForQuote $email)
    {
        /** @var $zipper AttachmentZipper */
        $zipper = $this->get(AttachmentZipper::class);
        $email->consolidateAttachments($zipper);
        $mailer = $this->get(MailerInterface::class);
        $mailer->send($email);
        $email->setSent();
    }

    /**
     * Email a QuotationRequest to potential suppliers.
     *
     * @Route("/purchasing/rfq/{id}/send/", name="rfq_send")
     * @Template("purchasing/quotation/request-send.html.twig")
     */
    public function sendAction(QuotationRequest $rfq, Request $request)
    {
        $this->denyAccessUnlessGranted([Role::ENGINEER, Role::PURCHASING]);
        $email = new RequestForQuote($rfq, $this->getDefaultCompany());
        $this->prepEmail($email);
        $form = $this->createForm(RequestForQuoteType::class, $email);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->dbm->beginTransaction();
            try {
                $this->sendEmail($email);
                $this->dbm->flushAndCommit();
                $this->logNotice("Email sent successfully.");
            } catch (EmailException $ex) {
                $this->dbm->rollBack();
                $this->logException($ex);
            } catch (\Exception $ex) {
                $this->dbm->rollBack();
                throw $ex;
            }

            return $this->redirectToRoute('rfq_view', [
                'id' => $rfq->getId(),
            ]);
        }

        $this->setReturnUri($this->getCurrentUri());
        return [
            'rfq' => $rfq,
            'form' => $form->createView(),
            'email' => $email,
        ];
    }

    /**
     * @Route("/purchasing/rfq/{id}/", name="rfq_delete")
     * @Method("DELETE")
     */
    public function deleteAction(QuotationRequest $rfq)
    {
        $this->denyAccessUnlessGranted([Role::PURCHASING, Role::ENGINEER]);
        if ($rfq->isSent()) {
            throw $this->badRequest("$rfq has been sent and cannot be deleted");
        }
        $msg = "$rfq deleted successfully.";
        $this->dbm->remove($rfq);
        $this->dbm->flush();
        $this->logNotice($msg);
        return $this->redirectToRoute('rfq_list');
    }
}
