<?php

declare(strict_types = 1);

namespace myrpc;

use myrpc\Exception\ServiceException;
use myrpc\Handler\Context\ContextFactoryInterface;
use myrpc\Handler\HandlerFactoryInterface;
use myrpc\Identity\IdentityFactoryInterface;
use myrpc\Request\RequestFactoryInterface;
use myrpc\Response\ResponseFactoryInterface;
use myrpc\Response\ResponseInterface;
use myrpc\Schema\SchemaFactoryInterface;
use Psr\Log\LoggerInterface;
use Throwable;

class ApiServer
{
    //TODO add factory
    public function __construct(
        protected readonly HandlerFactoryInterface $handlerFactory,
        protected readonly RequestFactoryInterface $requestFactory,
        protected readonly ResponseFactoryInterface $responseFactory,
        protected readonly IdentityFactoryInterface $identityFactory,
        protected readonly SchemaFactoryInterface $schemaFactory,
        protected readonly ContextFactoryInterface $handlerContextFactory,
        protected readonly LoggerInterface $logger
    ) {
    }

    public function run(mixed $payload = null): ResponseInterface
    {
        try {
            $request = $this->requestFactory->create($payload);
            $service = $request->getService();

            $requestId = $request->getRequestId();
            $authToken = $request->getIdentityToken();


            //TODO unit cover this context creation and passing

            $handler = $this->handlerFactory->create($service);
            $contextHandler = $this->handlerFactory->hasContext($handler);

            if ($contextHandler) {
                /**
                 * Context-aware handler
                 */
                $handler = $this->handlerFactory->withContext($contextHandler, $this->handlerContextFactory->create());
            }

            $identity = $this->identityFactory->create($authToken);

            $action    = $request->getAction();
            $arguments = $request->getArguments();

            $actionResult = $handler->action($action ?? '', $arguments, $identity);

            $responsePayload = $actionResult->getResponse();

            if ($actionResult->isError()) {
                return $this->responseFactory->createErrorResponse(
                    $responsePayload,
                    $actionResult->getErrorCode(),
                    $requestId
                );
            }

            /* @phan-suppress-next-line PhanCoalescingNeverUndefined */
            return $this->responseFactory->createSuccessResponse($responsePayload, $requestId ?? null);
        } catch (ServiceException $e) {
            $this->logger->error($e->getLogMessage());

            /** @psalm-suppress RedundantCast */

            /* @phan-suppress-next-line PhanCoalescingNeverUndefined */
            return $this->responseFactory->createErrorResponse(
                $e->getMessage(),
                (int) $e->getCode(),
                $requestId ?? null
            );
        } catch (Throwable $e) {
            $this->logger->critical($e->getMessage() . ' ' . $e->getTraceAsString());

            /* @phan-suppress-next-line PhanCoalescingNeverUndefined */
            return $this->responseFactory->createExceptionResponse($e, (int) $e->getCode(), $requestId ?? null);
        }
    }
}
