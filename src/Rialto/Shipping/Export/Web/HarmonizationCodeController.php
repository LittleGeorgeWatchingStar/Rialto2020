<?php

namespace Rialto\Shipping\Export\Web;

use Gumstix\Filetype\CsvFile;
use Rialto\Security\Role\Role;
use Rialto\Shipping\Export\HarmonizationCode;
use Rialto\Shipping\Export\Orm\HarmonizationCodeRepository;
use Rialto\Web\RialtoController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class HarmonizationCodeController extends RialtoController
{
    /**
     * @Route("/shipping/hscode/", name="hscode_list")
     * @Method("GET")
     * @Template("shipping/harmonizationCode/code-list.html.twig")
     */
    public function listAction()
    {
        $this->denyAccessUnlessGranted(Role::STOCK);

        $repo = $this->getRepo();
        $list = $repo->findAll();

        return [
            'codes' => $list,
            'upload' => $this->getUploadForm()->createView(),
            'enable' => $this->getEnableForm()->createView(),
        ];
    }

    /** @return HarmonizationCodeRepository */
    private function getRepo()
    {
        return $this->getRepository(HarmonizationCode::class);
    }

    /** @return FormInterface */
    private function getUploadForm()
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('hscode_upload'))
            ->add('file', FileType::class, [
                'constraints' => new Assert\File([
                    'maxSize' => '10M',
                    'mimeTypes' => [
                        'text/csv',
                        'text/tsv',
                        'text/plain',
                    ],
                ]),
                'label' => 'Upload CSV/TSV',
            ])
            ->add('upload', SubmitType::class)
            ->getForm();
    }

    /**
     * @Route("/shipping/hscode/", name="hscode_upload")
     * @Method("POST")
     * @Template("shipping/harmonizationCode/code-upload.html.twig")
     */
    public function uploadAction(Request $request)
    {
        $this->denyAccessUnlessGranted(Role::ADMIN);

        $form = $this->getUploadForm();

        $form->handleRequest($request);
        $errors = [];
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var $index HarmonizationCode[] */
            $index = $this->getRepo()->fetchIndex();

            /** @var $validator ValidatorInterface */
            $validator = $this->get(ValidatorInterface::class);

            /** @var $file UploadedFile */
            $file = $form->get('file')->getData();
            $csv = new CsvFile();
            $csv->parseFile($file->getRealPath());
            foreach ($csv as $rowNum => $row) {
                $id = $row[0];
                $code = isset($index[$id]) ? $index[$id] : new HarmonizationCode($id);
                $code->setName($row[1]);
                $code->setDescription($row[2]);

                $violations = $validator->validate($code);
                if (count($violations) > 0) {
                    $errors[$rowNum] = $violations;
                } else {
                    $this->dbm->persist($code);
                }
            }

            if (count($errors) == 0) {
                $this->dbm->flush();
                $count = number_format(count($csv));
                $this->logNotice("Uploaded $count codes successfully.");
                return $this->redirectToRoute('hscode_list');
            }
        }

        return [
            'form' => $form->createView(),
            'errors' => $errors,
        ];
    }

    /**
     * @Route("/shipping/hscode/enable/", name="hscode_enable")
     * @Method("POST")
     */
    public function enableAction(Request $request)
    {
        $this->denyAccessUnlessGranted(Role::ADMIN);
        $form = $this->getEnableForm();
        $form->handleRequest($request);
        $enable = $form->get('enable')->getData();
        $disable = $form->get('disable')->getData();
        if ($enable) {
            $qb = $this->getRepo()->createQueryBuilder('code');
            $qb->update()
                ->set('code.active', $qb->expr()->literal(true))
                ->where('code.id like :enable')
                ->setParameter('enable', $enable);
            $enabled = $qb->getQuery()->execute();
            $this->logNotice("Enabled $enabled codes matching '$enable'.");
        }
        if ($disable) {
            $qb = $this->getRepo()->createQueryBuilder('code');
            $qb->update()
                ->set('code.active', $qb->expr()->literal(false))
                ->where('code.id like :disable')
                ->setParameter('disable', $disable);
            $disabled = $qb->getQuery()->execute();
            $this->logNotice("Disabled $disabled codes matching '$disable'.");
        }
        return $this->redirectToRoute('hscode_list');
    }

    /** @return FormInterface */
    private function getEnableForm()
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('hscode_enable'))
            ->add('enable', TextType::class, [
                'required' => false
            ])
            ->add('disable', TextType::class, [
                'required' => false,
            ])
            ->add('submit', SubmitType::class)
            ->getForm();
    }

}
