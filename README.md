# Swow event dispatcher PSR-14 compatible (but opinionated)

Basic PSR-14 compatible (but opinionated) event dispatcher for usage with swow coroutines.

## Features
* Lazy loading event listeners
* Bring your own DI by implementing [Container-Mason](https://github.com/squid-it/container-mason) (unify how DI containers work)
* Build-in caching mechanism
* Priority listener support
* Buffered event handling
