<?php

use Symfony\Component\DependencyInjection\Argument\RewindableGenerator;

// This file has been auto-generated by the Symfony Dependency Injection Component for internal use.
// Returns the public 'console.command.rialto_manufacturing_task_cli_taskscommand' shared autowired service.

return $this->services['console.command.rialto_manufacturing_task_cli_taskscommand'] = new \Rialto\Manufacturing\Task\Cli\TasksCommand(${($_ = isset($this->services['Doctrine\\Common\\Persistence\\ObjectManager']) ? $this->services['Doctrine\\Common\\Persistence\\ObjectManager'] : $this->getObjectManagerService()) && false ?: '_'}, ${($_ = isset($this->services['Rialto\\Manufacturing\\Task\\ProductionTaskFactory']) ? $this->services['Rialto\\Manufacturing\\Task\\ProductionTaskFactory'] : $this->load('getProductionTaskFactoryService.php')) && false ?: '_'}, ${($_ = isset($this->services['monolog.logger.automation']) ? $this->services['monolog.logger.automation'] : $this->load('getMonolog_Logger_AutomationService.php')) && false ?: '_'});
