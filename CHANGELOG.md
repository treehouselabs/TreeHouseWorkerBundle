CHANGELOG
=========

## 2.0.0

### Changes

* Added Symfony 4 support
* #16 catch throwables instead of just exceptions

### Breaking changes

* Dropped support for PHP 5.5 and 5.6
* Dropped support for unmaintained Symfony versions. Supported versions are 2.8, 3.4, 4.1 and up,
  as per the [Symfony Roadmap](https://symfony.com/roadmap).

## 1.2.1

* #9  Ensure that a job retains its priority when it is rescheduled
* #10 Add ability to specify a new priority on rescheduled jobs
* #11 Add ability to set timeout for reservations


## 1.2.0

* Added Symfony3 support
* Added `worker:peek` command


## 1.1.1

* Added default ttr configuration


## 1.1.0

* Added event for buried jobs


## 1.0.1

* When using Monolog, logs are output to the console automatically


## 1.0.0

BC breaks from the previous [WorkerBundle][worker-bundle]:

[worker-bundle]: https://github.com/treehouselabs/FMWorkerBundle/

* Renamed the `FM` namespace to `TreeHouse`
* Removed `QueueManager::setExceptionHandler` and `QueueManager::getExceptionHandler`.
* The `$time` argument in `RescheduleException::create` no longer needs the `+`
  sign.
* Actions and executors are now combined in a dictionary. The action methods
  (`registerAction()`, `hasAction()` and `getActions()`) have therefore been
  removed from the `QueueManager`.
* Renamed JobExecutor to Executor:
   * `hasJobExecutor()` => `hasExecutor()` in `QueueManager`
   * `addJobExecutor()` => `addExecutor()` in `QueueManager`
   * `getJobExecutor()` => `getExecutor()` in `QueueManager`
* The `$payload` argument in `QueueManager::add()` is now required.
* The `$date` argument in `QueueManager::add()`and `QueueManager::addForObject()`
  has been replaced by a `$delay` argument.
* Renamed `QueueManager::get()` to `QueueManager::peek()` to better reflect the
  method that is used.
* The `$action` argument in `QueueManager::peek()` is now required.
* Renamed `QueueManager::executeJob()` to `QueueManager::execute()`.
* Renamed `QueueManager::process()` to `QueueManager::executeJob()`.
* Renamed `JobExecutorEvent` to `ExecutionEvent`. Also instead of the
  `QueueManager`, this event receives an executor and the result after the job
  has been executed.
* Renamed the `Queue` namespace to `Executor` to better reflect the classes in it.
  All references to `JobExecutor` and `ObjectPayloadInterface` must be updated.
* Renamed `JobExecutor` to `AbstractExecutor`.
* Removed `WorkerEvent`
* Renamed constants in WorkerEvents:
  * `PRE_EXECUTE_JOB` to `PRE_EXECUTE_ACTION`
  * `POST_EXECUTE_JOB` to `POST_EXECUTE_ACTION`
* Removed the logging aggregation feature: previously, executors could return a
  logger for which the messages would be relayed to the `QueueManager`'s logger.
  With the fine-grained control that Symfony now offers this is no longer needed.
  All references to the `WorkerBundle\Monolog` namespace must be removed.
* Executors now need to be tagged with `tree_house.worker.executor` instead of
  `fm_worker.queue.job_executor`.
* Removed `fm_worker.logger.handler` support
* Removed the `WorkerEvents::RUN_TERMINATE` event: use the console termination
  event instead.

Changes:

* Added ExecutorInterface
* Added configurable payloads
* Added convenience commands
* Listeners to the 'pre-execute' event may change the payload.
* Listeners to the 'post-execute' event may change the result.
