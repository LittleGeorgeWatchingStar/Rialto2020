<?php

namespace Rialto\Web;

use Doctrine\Common\Persistence\Mapping\MappingException;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\ORMInvalidArgumentException;
use ErrorException;
use Rialto\Alert\AlertMessage;
use Rialto\Company\Company;
use Rialto\Database\Orm\DbManager;
use Rialto\Database\Orm\DoctrineDbManager;
use Rialto\Entity\RialtoEntity;
use Rialto\Logging\FlashLogger;
use Rialto\Security\User\User;
use Rialto\Stock\Facility\Facility;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Templating\EngineInterface;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Twig_Environment;
use UnexpectedValueException;


/**
 * Base class for all Rialto controllers.
 */
abstract class RialtoController extends Controller
{
    /** @var DoctrineDbManager */
    protected $dbm;

    /**
     * {@inheritdoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        parent::setContainer($container);
        $this->dbm = $container->get(DbManager::class);
        if ($container) {
            $this->init($container);
        }
    }

    /**
     * Initialize any additional properties that the controller needs.
     */
    protected function init(ContainerInterface $container)
    {
        // To be overridden by subclasses as needed.
    }

    /**
     * @return EntityManagerInterface|ObjectManager
     */
    protected function manager(): EntityManagerInterface
    {
        return $this->getDoctrine()->getManager();
    }

    /**
     * @param string $name The class name
     * @return EntityRepository|ObjectRepository
     */
    protected function getRepository(string $name): EntityRepository
    {
        return $this->getDoctrine()->getRepository($name);
    }

    /**
     * @param string $name The fully-qualified class name
     * @param string|int $id
     * @return RialtoEntity|object|null
     *  Null if the requested entity cannot be found.
     */
    protected function findEntity(string $name, $id)
    {
        /** @var $dbm DbManager */
        $dbm = $this->get(DbManager::class);
        return $dbm->find($name, $id);
    }

    /**
     * @param string $name The fully-qualified class name
     * @param string|int $id
     * @return RialtoEntity
     * @throws NotFoundHttpException if the requested entity cannot be found.
     */
    protected function needEntity(string $name, $id)
    {
        try {
            $entity = $this->findEntity($name, $id);
        } catch (ErrorException $ex) {
            throw $this->notFound("Invalid record class", $ex);
        } catch (MappingException $ex) {
            throw $this->notFound("Invalid record class", $ex);
        } catch (ORMInvalidArgumentException $ex) {
            throw $this->notFound("Invalid ID", $ex);
        }
        if (!$entity) {
            throw $this->notFound("No matching record");
        }
        return $entity;
    }

    /**
     * @param string $className The name of the class
     * @param string $param The param containing the entity's ID
     * @return RialtoEntity
     */
    protected function needEntityFromRequest(string $className, string $param, Request $request = null)
    {
        $entity = $this->getEntityFromRequest($className, $param, $request);
        if ($entity) {
            return $entity;
        }
        throw $this->badRequest("Missing required parameter \"$param\"");
    }

    /**
     * Fetch an entity from the database based on a request parameter.
     * Return null if the parameter is not given.
     *
     * @param string $className The name of the class
     * @param string $param The param containing the entity's ID
     * @return RialtoEntity|null
     */
    protected function getEntityFromRequest(string $className, string $param, Request $request = null)
    {
        $request = $request ?: $this->getCurrentRequest();
        $id = $request->get($param);
        if (!$id) {
            return null;
        }
        $entity = $this->findEntity($className, $id);
        if (!$entity) {
            throw $this->notFound("No such $className $id");
        }
        return $entity;
    }

    /**
     * @throws UnexpectedValueException if there is no current user.
     */
    protected function getCurrentUser(): User
    {
        $user = $this->getUser();
        if ($user) {
            return $user;
        }
        throw new UnexpectedValueException("There is no current user");
    }

    protected function getCurrentRequest(): Request
    {
        $stack = $this->get(RequestStack::class);
        /* @var $stack RequestStack */
        return $stack->getCurrentRequest();
    }

    protected function getCurrentUri(): string
    {
        $request = $this->getCurrentRequest();
        return $request->getUri();
    }

    protected function getDefaultCompany(): Company
    {
        return Company::findDefault($this->dbm);
    }

    protected function getHeadquarters(): Facility
    {
        return Facility::fetchHeadquarters($this->dbm);
    }

    protected function redirect($url, $status = 302)
    {
        assertion(is_string($url), "Redirect URL must be a string");
        return parent::redirect($url, $status);
    }

    protected function renderString($templateString, array $params = [])
    {
        /** @var $twig Twig_Environment */
        $twig = $this->get(Twig_Environment::class);
        $template = $twig->createTemplate($templateString);
        $body = $template->render($params);
        return new Response($body);
    }

    /**
     * Returns the URI to return to after some action, such as submitting
     * or canceling a form.
     *
     * @param string $default If there is no return URI set, fall back to $default
     * @return string The return URI
     * @see setReturnUri()
     */
    protected function getReturnUri($default)
    {
        return $this->get(SessionInterface::class)->get('returnTo', $default);
    }

    /**
     * Sets the URI to return to after some action, such as submitting
     * or canceling a form.
     *
     * @param string $uri
     * @see getReturnUri()
     */
    protected function setReturnUri($uri = null)
    {
        if (!$uri) {
            $uri = $this->getCurrentUri();
        }
        $this->get(SessionInterface::class)->set('returnTo', $uri);
    }

    protected function logNotice($msg)
    {
        $this->flashLogger()->notice($msg);
    }

    /** @return FlashLogger|object */
    private function flashLogger()
    {
        return $this->get(FlashLogger::class);
    }

    protected function logWarning($msg)
    {
        $this->flashLogger()->warning($msg);
    }

    /**
     * @return int The number of warnings logged.
     */
    protected function logWarnings(array $warnings)
    {
        foreach ($warnings as $msg) {
            $this->logWarning($msg);
        }
        return count($warnings);
    }

    protected function logError($msg)
    {
        $this->flashLogger()->error($msg);
    }

    protected function logException(\Exception $ex)
    {
        $msg = rtrim($ex->getMessage(), '.') . '.';
        $this->flashLogger()->critical($msg, [
            'trace' => $ex->getTraceAsString()
        ]);
    }

    protected function logErrors($errors)
    {
        foreach ($errors as $error) {
            if ($error instanceof ConstraintViolationInterface) {
                $error = $error->getMessage();
            } elseif ($error instanceof FormError) {
                $error = $error->getMessage();
            }
            $this->logError($error);
        }
    }

    protected function logAlert(AlertMessage $alert)
    {
        $this->flashLogger()->logAlert($alert);
    }

    /** @return FormBuilderInterface */
    protected function createNamedBuilder($name, $data = null, $options = [])
    {
        return $this->get(FormFactoryInterface::class)->createNamedBuilder(
            $name, FormType::class, $data, $options
        );
    }

    protected function notFound($msg = 'Not found', \Exception $previous = null)
    {
        return $this->createNotFoundException($msg, $previous);
    }

    protected function badRequest($msg = 'Bad request', \Exception $previous = null)
    {
        return new BadRequestHttpException($msg, $previous);
    }

    protected function forbidden($msg = 'Forbidden')
    {
        return new AccessDeniedHttpException($msg);
    }

    /**
     * Check the CSRF token in the request and throw a 400 if it is not valid.
     *
     * @param string $intention
     */
    protected function checkCsrf($intention, Request $request, $tokenName = '_token')
    {
        if (!$this->isCsrfTokenValid($intention, $request->get($tokenName))) {
            throw $this->badRequest("Invalid CSRF token");
        }
    }

    /**
     * Rialto uses the event dispatcher enough to justify this shortcut.
     *
     * @return EventDispatcherInterface|object The event dispatcher
     */
    protected function dispatcher()
    {
        return $this->get(EventDispatcherInterface::class);
    }

    protected function dispatchEvent($eventName, Event $event = null)
    {
        $this->dispatcher()->dispatch($eventName, $event);
    }

    protected function getTemplating(): EngineInterface
    {
        return $this->get('templating');
    }
}
