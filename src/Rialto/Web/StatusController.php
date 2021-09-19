<?php

namespace Rialto\Web;


use Doctrine\DBAL\Connection;
use Rialto\Security\Role\Role;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Response;

/**
 * For viewing the system status. Admin only.
 *
 * @Route("/status")
 */
class StatusController extends RialtoController
{
    /**
     * @Route("/phpinfo/", name="status_phpinfo")
     */
    public function phpinfoAction()
    {
        $this->denyAccessUnlessGranted(Role::ADMIN);
        ob_start();
        phpinfo();
        $output = ob_get_clean();
        return new Response($output);
    }

    /**
     * Show MySQL database configuration.
     *
     * @Route("/mysql/", name="status_mysql")
     * @Template("core/status/mysql.html.twig")
     */
    public function mysqlAction()
    {
        $this->denyAccessUnlessGranted(Role::ADMIN);
        /** @var $conn Connection */
        $conn = $this->getDoctrine()->getConnection();
        $variables = $conn->executeQuery("show variables")->fetchAll();
        $tableStatus = $conn->executeQuery("show table status")->fetchAll();
        return [
            'variables' => $variables,
            'tableStatus' => $tableStatus,
        ];
    }
}
