#Infrastructure Service Definitions

This directory is intended for registering services from the `Infrastructure`
namespace either as an implementation of a *port* service or a dependency of
another infrastructure service.

Infrastructure services should never be directly referenced by any client code
from the `Geppetto` namespace. They should instead implement a corresponding
interface from the `Geppetto\Port` namespace and be registered as the class
for the interface implemented, for example:

```yaml
Geppetto\Port\CommandBus\CommandBus:
    class: Infrastructure\CommandBus\TacticianCommandBus
```