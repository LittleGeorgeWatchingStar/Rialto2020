<?php

namespace Rialto\Magento2\Storefront\Web;

use Rialto\Magento2\Api\Rest\RestApiFactory;
use Rialto\Magento2\Firewall\MagentoAuthenticator;
use Rialto\Magento2\Storefront\Storefront;
use Rialto\Security\Role\Role;
use Rialto\Web\RialtoController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * For managing Magento storefronts.
 */
class StorefrontController extends RialtoController
{
    /**
     * @var RestApiFactory
     */
    private $apiFactory;

    protected function init(ContainerInterface $container)
    {
        $this->apiFactory = $this->get(RestApiFactory::class);
    }

    /**
     * List storefronts.
     *
     * @Route("/magento2/storefront/", name="magento2_storefront_list")
     * @Template("magento2/storefront-list.html.twig")
     */
    public function listAction()
    {
        $this->denyAccessUnlessGranted(Role::ADMIN);
        $repo = $this->getRepository(Storefront::class);
        return [
            'storefronts' => $repo->findAll(),
        ];
    }

    /**
     * Create a new magento2 storefront.
     *
     * @Route("/magento2/storefront/create/", name="magento2_storefront_create")
     * @Template("magento2/storefront-create.html.twig")
     */
    public function createAction(Request $request)
    {
        $this->denyAccessUnlessGranted(Role::ADMIN);
        $store = new Storefront();
        $store->setShipFromFacility($this->getHeadquarters()); // sensible default
        return $this->processForm($store, $request, 'created');
    }


    private function processForm(Storefront $store, Request $request, $updated = 'updated')
    {
        $form = $this->createForm(StorefrontType::class, $store);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('generateAPI')->isClicked()) {
                $store->regenerateApiKey();
            }
            $this->dbm->persist($store);
            $this->dbm->flush();
            $this->logNotice("$store $updated successfully.");
            return $this->redirectToRoute('magento2_storefront_edit', [
                'id' => $store->getId(),
            ]);
        }
        return [
            'store' => $store,
            'form' => $form->createView(),
            'keyParam' => MagentoAuthenticator::KEY_QUERY_PARAM,
        ];
    }

    /**
     * Edit an existing storefront.
     *
     * @Route("/magento2/storefront/{id}/", name="magento2_storefront_edit")
     * @Method({"GET", "POST"})
     * @Template("magento2/storefront-edit.html.twig")
     */
    public function editAction(Storefront $store, Request $request)
    {
        $this->denyAccessUnlessGranted(Role::ADMIN);
        return $this->processForm($store, $request);
    }

    /**
     * Delete a storefront.
     *
     * @Route("/magento2/storefront/{id}/", name="magento2_storefront_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Storefront $store)
    {
        $this->denyAccessUnlessGranted(Role::ADMIN);
        $this->dbm->remove($store);
        $this->dbm->flush();
        $this->logNotice("Deleted $store successfully.");
        return $this->redirectToRoute('magento2_storefront_list');
    }

    /**
     * Ensure that we can communicate with the given storefront.
     *
     * @Route("/magento2/api-test/{id}/", name="magento2_apitest")
     * @Method("PUT")
     */
    public function testAction(Storefront $store)
    {
        $this->denyAccessUnlessGranted(Role::ADMIN);
        $response = $this->apiFactory->testApiConnection($store);
        return new Response($response->getReasonPhrase(), $response->getStatusCode());
    }
}
