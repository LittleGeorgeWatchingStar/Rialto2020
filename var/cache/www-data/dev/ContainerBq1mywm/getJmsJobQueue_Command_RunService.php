<?php

use Symfony\Component\DependencyInjection\Argument\RewindableGenerator;

// This file has been auto-generated by the Symfony Dependency Injection Component for internal use.
// Returns the private 'jms_job_queue.command.run' shared service.

$this->services['jms_job_queue.command.run'] = $instance = new \JMS\JobQueueBundle\Command\RunCommand();

$instance->setName('jms-job-queue:run');

return $instance;
