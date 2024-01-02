<!---
This file is part of the sshilko/php-api-myrpc package.

(c) Sergei Shilko <contact@sshilko.com>

MIT License

For the full copyright and license information, please view the LICENSE
file that was distributed with this source code.

@license https://opensource.org/licenses/mit-license.php MIT
-->
(Beta) MyAPI - Component
=================
<p align="left">
	<img src="https://img.shields.io/badge/status-active-success" alt="Project status - beta">
	<a href="https://packagist.org/packages/sshilko/php-api-myrpc-beta"><img src="https://poser.pugx.org/sshilko/php-api-myrpc-beta/v/stable" alt="Latest Stable Version"></a>
	<a href="https://packagist.org/packages/sshilko/php-api-myrpc-beta/stats"><img src="https://poser.pugx.org/sshilko/php-api-myrpc-beta/downloads" alt="Total Downloads"></a>
	<a href="https://packagist.org/packages/sshilko/php-api-myrpc-beta"><img src="https://poser.pugx.org/sshilko/php-api-myrpc-beta/require/php" alt="PHP Required Version"></a>
	<a href="https://choosealicense.com/licenses/mit/"><img src="https://poser.pugx.org/sshilko/php-api-myrpc-beta/license" alt="MIT License"></a>
    <a href="https://psalm.dev/docs/running_psalm/command_line_usage/#shepherd">
    <img src="https://shepherd.dev/github/sshilko/php-api-myrpc-beta/coverage.svg" alt="Psalm Coverage"></a>
    <img src="https://hits.seeyoufarm.com/api/count/incr/badge.svg?url=https%3A%2F%2Fgithub.com%2Fsshilko%2Fphp-sql-mydb&count_bg=%2379C83D&title_bg=%23555555&icon=&icon_color=%23E7E7E7&title=hits&edge_flat=false"/>
    <img src="https://img.shields.io/github/languages/code-size/sshilko/php-api-myrpc-beta" alt="Code size">
    <br />
    <img src="https://raw.githubusercontent.com/sshilko/php-api-myrpc-beta/pages/php/phpunit/phpunit-coverage-badge.svg" alt="PHPUnit coverage" />
    <img src="https://raw.githubusercontent.com/sshilko/php-api-myrpc-beta/pages/php/phpunit/phpunit-coverage-badge-classes.svg" alt="PHPUnit classes coverage" />
    <img src="https://raw.githubusercontent.com/sshilko/php-api-myrpc-beta/pages/php/phpunit/phpunit-coverage-badge-lines.svg" alt="PHPUnit lines coverage" />
    <img src="https://raw.githubusercontent.com/sshilko/php-api-myrpc-beta/pages/php/phpunit/phpunit-coverage-badge-methods.svg" alt="PHPUnit methods coverage" />
    <br/>
    <a href="https://sshilko.com/php-api-myrpc-beta/php/phan/"><img src="https://github.com/sshilko/php-api-myrpc-beta/actions/workflows/phpphan.yml/badge.svg" alt="Phan build"></a>
    <a href="https://sshilko.com/php-api-myrpc-beta/php/psalm/"><img src="https://github.com/sshilko/php-api-myrpc-beta/actions/workflows/phppsalm.yml/badge.svg" alt="Psalm build"></a>
    <a href="https://sshilko.com/php-api-myrpc-beta/php/phpmd/"><img src="https://github.com/sshilko/php-api-myrpc-beta/actions/workflows/phpmd.yml/badge.svg" alt="PHPMd build"></a>
    <a href="https://sshilko.com/php-api-myrpc-beta/php/phpstan/"><img src="https://github.com/sshilko/php-api-myrpc-beta/actions/workflows/phpstan.yml/badge.svg" alt="PHPStan build"></a>
    <a href="https://sshilko.com/php-api-myrpc-beta/php/phpcs/"><img src="https://github.com/sshilko/php-api-myrpc-beta/actions/workflows/phpcs.yml/badge.svg" alt="PHPCodeSniffer build"></a>
    <a href="https://sshilko.com/php-api-myrpc-beta/php/phpdoc/"><img src="https://github.com/sshilko/php-api-myrpc-beta/actions/workflows/phpdoc.yml/badge.svg" alt="PHPDocumentor build"></a>
    <a href="https://sshilko.com/php-api-myrpc-beta/php/pdepend/"><img src="https://github.com/sshilko/php-api-myrpc-beta/actions/workflows/phppdepend.yml/badge.svg" alt="Pdepend build"></a>
    <a href="https://sshilko.com/php-api-myrpc-beta/php/phpunit/html/"><img src="https://github.com/sshilko/php-api-myrpc-beta/actions/workflows/phpunit.yml/badge.svg" alt="PHPUnit build"></a>
    <a href="https://github.com/sshilko/php-api-myrpc-beta/actions/workflows/phpunit81.yml"><img src="https://github.com/sshilko/php-api-myrpc-beta/actions/workflows/phpunit81.yml/badge.svg" alt="8.1 PHPUnit build"></a>
    <a href="https://github.com/sshilko/php-api-myrpc-beta/actions/workflows/github-pages.yml"><img src="https://github.com/sshilko/php-api-myrpc-beta/actions/workflows/github-pages.yml/badge.svg" alt="GithubPages build"></a>
    <br/>
    </p>
</p>

PHP [JSON-RPC](https://www.jsonrpc.org) server and client with json-schema auto-generation and validation using DTO 

#### Installation

Please wait for initial public release at `sshilko/php-api-myrpc`.
```
composer require sshilko/php-api-myrpc-beta
```

#### Compatibility

- PHP >= 8.1

#### Why RPC

Over the years, first in 2007-2010 json became popular and took over the XML in the RPC space as more readable
and browser/javascript friendly format. Few proposals were created to unify the request/response for the json rpc api.

Later [REST](https://en.wikipedia.org/wiki/Representational_state_transfer) took over by offering resource endpoints
that fit really nice for simple CRUD apps, but it required more upfront investment into designing your APIs,
and as API codebase grew and usually in monolith new functionality been added, the complexity grew fast.

REST API and JSON-RPC API comparison:
- JSON-RPC is more loosely typed with [OSI-4 Transport layer](https://en.wikipedia.org/wiki/OSI_model)
  - REST is stricter and defines OSI-7 Application layer
- JSON-RPC reuses only json and [json-schema](https://json-schema.org) in contract definition
  - REST may use multiple standards like [Swagger/OpenAPI](https://swagger.io), XML, JSON, [HATEOAS](https://en.wikipedia.org/wiki/HATEOAS), [JSON-LD](https://json-ld.org) and Hydra (Hydra is an extension of JSON-LD, essentially another OpenAPI standard) 
- JSON-RPC does not say anything about caching, as it does not rely on HTTP layer
  - REST defines requirements about behaviour in caching, endpoint naming, Idempotence - in reality none of this is guaranteed by any framework, up to exact implementation by developer
- REST api defines HTTP status codes to be used for response, which may lead to "faster" processing on client
  - in practice most of the time the error message or error trace is still dispatched in the body and will be parsed by client
  - in RPC, we do not rely on headers or cookies, error message format is documented in standard, error logging and response/transport are different concerns and should not be mixed

TLDR
REST requires good knowledge in both technology and domain to design proper API, enforces strict implementation rules.
GRPC requires less preparation to start with, offers typed schema validation for input and output, implementation is flexible.

in 2022 overall JSON-RPC evolved into [Google gRPC](https://grpc.io)
- both are binary formats (either with HTTP1 or HTTP2 compressions) or json-rpc wrapped in [MsgPack](https://msgpack.org/index.html)
- both are strictly typed, with json-rpc requires manual schema validation (library concern)
- both provide some sort of auto generated schema for clients to build API contract

JSON-RPC may not be as widespread as gRPC, but it is
- simpler to adopt
- works as reliable as HTTP (network guarantees), HTTP is available in any mobile/web/iot device
- all existing HTTP tooling can be reused (initial json benefits) during development
- one can easily adopt REST, gRPC or Swagger if already implemented JSON-RPC (rarely the case other way)
- [OpenAPI v3](https://swagger.io/specification/) Swagger is almost compatible with JSON-RPC 

#### What is the best use-case for this library

- [Facade](https://en.wikipedia.org/wiki/Facade_pattern) for the business logic
- Simplicity focused, not a generic one-fit-all solution
- Minimum 3rd party dependencies
- No compilation required
- JSON-Schema auto generation for clients

#### Out of scope

This library is **not** intending to become a framework, to *keep focus* and minimize codebase, it does **NOT provide**:

- HTML or other easy way to navigate the API, this can be achieved with 3rd party json-schema transpilers
- Non JSON based API, this implementation focuses on JSON representation
  - other wrapping/format is supported, please contribute your implementations
- Authorization, authentication, security or other components required for full-fledged user-facing web-application

please re-use existing solutions that best fit your requirements.

#### Why this library exists

* [APIPlatform](https://api-platform.com) is overcomplicated and long-term commitment, 
  * depends **heavily** on Doctrine and Symfony
  * large codebase with hidden complexity and maintainability issues
* [zendframework/zend-json-server](https://github.com/zendframework/zend-json-server) abandoned and non-flexible
* Simple facade for business logic with I/O validation is always needed in API-first application
* Re-use the power of strict typed I/O with standard error messages that PHP8 provides
* Simple, fast, easy to read and extend for your needs, easy to iterate and contribute to

#### Future roadmap

- 1.0 stable release

#### Contributing

* Please read [contributing](CONTRIBUTING) document

#### Authors

Sergei Shilko <contact@sshilko.com>
