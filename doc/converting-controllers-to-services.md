Converting Container-Aware Controllers to Services
==================================================


Service Registration to Enable Constructor Injection
----------------------------------------------------

* Register the controller class as a service with the 
`controller.service_arguments` tag, for example:
```yaml
Rialto\Security\User\Web\UserController:
    tags: ['controller.service_arguments']
    # Any other configuration
```

* Convert the `RialtoController::init` override to a 
constructor, including an `EntityManagerInterface` to replace
the one set in `RialtoController` for example:
```php
protected function init()
{
    $this->repo = $this->em->getRepository(User::class);
    $this->sync = $this->get(UserSync::class);
    $this->commandBus = $this->get(CommandBus::class);
}
```
becomes
```php
public function __construct(EntityManagerInterface $em,
                            UserSync $userSync,
                            CommandBus $commandBus)
{
    $this->entityManager = $em;
    $this->repo = $this->entityManager->getRepository(User::class);
    $this->sync = $userSync;
    $this->commandBus = $commandBus;
}
```

* If a dependency is only required for one controller method, consider passing
it as an argument to the method rather than the constructor to reduce the number
of private members in the controller, for example:
```php
public function syncAction(UserSync $userSync)
{
    $user = $this->requireCurrentUser();
    $userSync->sync($user);
    $this->entityManager->flush();
    return View::create(new UserDetail($user));
}
```
