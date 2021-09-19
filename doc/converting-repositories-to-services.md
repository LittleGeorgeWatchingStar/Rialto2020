Converting EntityRepository-Aware Repositories to Services
==========================================================

Replace Inheritance With Composition
------------------------------------
* Rather than extending `EntityRepository`, write the repository class
such that it instead takes an `EntityManagerInterface` in the constructor
and uses it to fetch the base repository for the entity, for example:
```php
class GroupRepo
{
    /** @var ObjectRepository */
    private $repo;

    public function __construct(EntityManagerInterface $em)
    {
        $this->repo = $em->getRepository(Group::class);
    }
    
    // Use $this->repo in explicit repository methods.
}
```

* Remove the `repository-class` reference to the repository in the
`<entity>.orm.xml` mapping, otherwise the new constructor will infinitely 
recurse from the `getRepository` call. 


The repository class can now be registered and configured like any other
service in `app/config/*.yaml` and injected into other services without
the need to depend on `EntityManagerInterface` and call `getRepository`.
