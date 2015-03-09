# About #
PcntlJob is simple PCNTL wrapper which allows to create child process in a friendly way.

Example:

```php
$childProcessesCount = 10;
$job = new Job($childProcessesCount);
// if parseRSS should be executed as class method in context, (example: $this)
$job->create(array($this, 'parseRSS'), array($href));
// or call as function
$job->create('parseRSS', array($href));
```

## Attention ##
In child processes all active connections will be lost or their behavior becomes unpredictable (in my case: mysql can read but cannot write, redis loses all). 
So they should be reinitialized.