# CQRS Flow - Command Bus and Query Bus for Laravel

An advanced Command Bus and Query Bus implementation for Laravel with CQRS support. Provides type-safe command/query handling, asynchronous processing, middleware pipeline, automatic handler discovery, and transaction management.

---

## ⚠️ Critical Important!

**All command and query handlers MUST be marked with corresponding attributes:**
- `#[AsCommandHandler(command: YourCommand::class)]` - for command handlers
- `#[AsQueryHandler(command: YourQuery::class)]` - for query handlers

**Without these attributes, the system will not be able to find handlers at application startup, as the class map is created statically during initialization.**

---

## ✨ Features

- 🎯 **CQRS Pattern** - Clear separation between Commands (write) and Queries (read)
- 🔒 **Type Safety** - Full PHP Generics support with typed results
- ⚡ **Asynchronous Processing** - Attribute-based async commands with Laravel Queue
- 🔄 **Middleware Pipeline** - Extensible middleware system for cross-cutting concerns
- 🔍 **Auto-discovery** - Automatic handler discovery through reflection
- 🗃️ **Transaction Management** - Automatic DB transaction wrapping
- 📊 **Built-in Logging** - Included middleware for request/response logging

---

## 🚀 Installation

Via Composer:

```bash
composer require nikolay/cqrs-flow
```

The package is automatically registered with Laravel service container through `CommandBusServiceProvider`.

---

## 🚀 Quick Start

### 1. Create a Command

```php
<?php

namespace App\Commands;

use Paltorik\CqrsFlow\Contracts\CommandInterface;

class CreateUserCommand implements CommandInterface
{
    public function __construct(
        public readonly string $name,
        public readonly string $email,
        public readonly string $password,
    ) {}
}
```

### 2. Create a Command Handler

```php
<?php

namespace App\Handlers;

use App\Commands\CreateUserCommand;
use App\Models\User;
use Paltorik\CqrsFlow\Contracts\CommandHandlerInterface;
use Paltorik\CqrsFlow\Attributes\AsCommandHandler;

#[AsCommandHandler(command: CreateUserCommand::class, inTransaction: true)]
class CreateUserHandler implements CommandHandlerInterface
{
    public function handle(CreateUserCommand $command): User
    {
        return User::create([
            'name' => $command->name,
            'email' => $command->email,
            'password' => bcrypt($command->password),
        ]);
    }
}
```

> **⚠️ Important!** The `#[AsCommandHandler]` attribute is mandatory for automatic handler discovery. Without it, the command will not be found during execution.

### 3. Dispatch the Command

```php
<?php

namespace App\Http\Controllers;

use App\Commands\CreateUserCommand;
use Paltorik\CqrsFlow\Contracts\CommandBusInterface;

class UserController extends Controller
{
    public function store(CommandBusInterface $commandBus)
    {
        $user = $commandBus->dispatch(
            new CreateUserCommand(
                name: 'John Doe',
                email: 'john@example.com',
                password: 'secret123'
            )
        );

        return response()->json($user);
    }
}
```

---

## 🚀 Advanced Usage

### Asynchronous Commands

Mark commands for asynchronous processing using the `#[AsyncCommand]` attribute:

```php
<?php

use Paltorik\CqrsFlow\Attributes\AsyncCommand;
use Paltorik\CqrsFlow\Contracts\CommandInterface;

#[AsyncCommand(queue: 'emails', delaySeconds: 30)]
class SendWelcomeEmailCommand implements CommandInterface
{
    public function __construct(
        public readonly int $userId,
        public readonly string $template,
    ) {}
}
```

### Query Bus

Create read-only operations using Query Bus:

```php
<?php

// Query
class GetUserQuery implements QueryInterface
{
    public function __construct(public readonly int $userId) {}
}

// Handler
use Paltorik\CqrsFlow\Attributes\AsQueryHandler;

#[AsQueryHandler(command: GetUserQuery::class)]
class GetUserHandler implements QueryHandlerInterface
{
    public function handle(GetUserQuery $query): User
    {
        return User::findOrFail($query->userId);
    }
}

// Usage
public function show(int $id, QueryBusInterface $queryBus)
{
    $user = $queryBus->ask(new GetUserQuery($id));
    return response()->json($user);
}
```

> **⚠️ Important!** The `#[AsQueryHandler]` attribute is mandatory for automatic query handler discovery.

### Transaction Management

Commands can be automatically wrapped in database transactions:

```php
<?php

namespace App\Handlers;

use Paltorik\CqrsFlow\Attributes\Transaction;

#[Transaction]
class CreateUserWithProfileHandler implements CommandHandlerInterface
{
    public function handle(CreateUserWithProfileCommand $command): User
    {
        // This entire method runs within a DB transaction
        $user = User::create($command->userData);
        $user->profile()->create($command->profileData);
        
        return $user;
    }
}
```

### Custom Middleware

Create custom middleware for cross-cutting concerns:

```php
<?php

namespace App\Middleware;

use Paltorik\CqrsFlow\Contracts\MiddlewareInterface;

class ValidationMiddleware implements MiddlewareInterface
{
    public function handle($command, \Closure $next)
    {
        // Perform validation
        $this->validate($command);
        
        // Continue to next middleware or handler
        return $next($command);
    }
    
    private function validate($command): void
    {
        // Your validation logic
    }
}
```

Register middleware in your service provider:

```php
public function register(): void
{
    $this->app->extend(MiddlewarePipeline::class, function ($pipeline) {
        return $pipeline->pipe(new ValidationMiddleware());
    });
}
```

---

## ⚙️ Configuration

Publish the configuration file:

```bash
php artisan vendor:publish --tag=flow
```

This will create `config/flow.php` where you can configure:

- Default queue names for async commands
- Middleware configuration
- Handler discovery paths
- Logging settings

---

## 🔍 Handler Discovery

The package automatically discovers handlers using reflection. Generate a handler map for better performance:

```bash
php artisan command:map
```

This creates an optimized map of commands/queries to their handlers, reducing reflection overhead in production.

---

## 🛡️ Type Safety

The package uses PHP Generics for full type safety:

```php
/**
 * @template TResult
 * @implements CommandInterface<User>
 */
class CreateUserCommand implements CommandInterface
{
    // Command returns User type
}

/**
 * @template TCommand of CreateUserCommand
 * @template TResult of User
 * @implements CommandHandlerInterface<CreateUserCommand, User>
 */
class CreateUserHandler implements CommandHandlerInterface
{
    public function handle(CreateUserCommand $command): User
    {
        // Full typing - IDE knows User is returned
    }
}
```

---

## 🏗️ Architecture Integration

This package easily integrates with Domain-Driven Design architectures:

```php
// Controller layer
public function store(CreateUserRequest $request, CommandBusInterface $commandBus)
{
    $dto = UserStoreDTO::fromArray($request->validated());
    
    $user = $commandBus->dispatch(
        new CreateUserCommand($dto)
    );
    
    return fractal($user, new UserTransformer())->respond();
}

// Handler coordinates with ServiceDomain
class CreateUserHandler implements CommandHandlerInterface
{
    public function __construct(
        private UserServiceDomain $userService,
        private NotificationServiceDomain $notificationService,
    ) {}
    
    public function handle(CreateUserCommand $command): User
    {
        $user = $this->userService->createUser($command->dto);
        $this->notificationService->sendWelcomeEmail($user);
        
        return $user;
    }
}
```

---

## 📋 Best Practices

### Commands vs Queries

- **Commands**: Use for operations that change state (create, update, delete)
- **Queries**: Use for read-only operations that return data
- Commands can be asynchronous, Queries should always be synchronous
- Commands may not return data, Queries always return data

### Handler Organization

```
app/
├── Commands/
│   ├── User/
│   │   ├── CreateUserCommand.php
│   │   └── UpdateUserCommand.php
│   └── Order/
│       └── ProcessOrderCommand.php
├── Queries/
│   ├── User/
│   │   └── GetUserQuery.php
│   └── Order/
│       └── GetOrderQuery.php
└── Handlers/
    ├── User/
    │   ├── CreateUserHandler.php
    │   ├── UpdateUserHandler.php
    │   └── GetUserHandler.php
    └── Order/
        ├── ProcessOrderHandler.php
        └── GetOrderHandler.php
```

### Error Handling

```php
class CreateUserHandler implements CommandHandlerInterface
{
    public function handle(CreateUserCommand $command): User
    {
        try {
            return User::create($command->toArray());
        } catch (QueryException $e) {
            if ($e->getCode() === '23000') { // Duplicate entry
                throw new UserAlreadyExistsException($command->email);
            }
            throw $e;
        }
    }
}
```

---

## 🧪 Testing

Test your commands and handlers in isolation:

```php
class CreateUserHandlerTest extends TestCase
{
    public function test_creates_user_successfully()
    {
        $command = new CreateUserCommand(
            name: 'John Doe',
            email: 'john@example.com',
            password: 'secret123'
        );
        
        $handler = new CreateUserHandler();
        $user = $handler->handle($command);
        
        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals('John Doe', $user->name);
        $this->assertEquals('john@example.com', $user->email);
    }
}
```

Integration testing with command bus:

```php
class CommandBusIntegrationTest extends TestCase
{
    public function test_command_bus_dispatches_command()
    {
        $commandBus = app(CommandBusInterface::class);
        
        $user = $commandBus->dispatch(
            new CreateUserCommand(
                name: 'Jane Doe',
                email: 'jane@example.com',
                password: 'secret456'
            )
        );
        
        $this->assertDatabaseHas('users', [
            'email' => 'jane@example.com'
        ]);
    }
}
```

---

## ⚡ Performance Considerations

1. **Handler Map Generation**: Always run `php artisan command:map` in production
2. **Asynchronous Processing**: Use async commands for heavy operations
3. **Middleware**: Keep middleware lightweight to avoid bottlenecks
4. **Transactions**: Use transactions only when necessary for data consistency

---

## 🔗 Integration with Appocore API

The package is integrated into the Appocore API architecture and follows established patterns:

### Usage with DTOs
```php
// Controller receives validated data through DTO
public function store(CreateUserRequest $request, CommandBusInterface $commandBus)
{
    $dto = UserStoreDTO::fromArray($request->validated());
    
    $user = $commandBus->dispatch(
        new CreateUserCommand($dto)
    );
    
    return fractal($user, new UserTransformer())->respond();
}
```

### Project Structure Placement
- **Commands/Queries**: `app/Service/Processes/Commands/` and `app/Service/Processes/Queries/`
- **Handlers**: `app/Service/Processes/Handler/`
- Integration with ServiceDomain for business logic
- Automatic registration through ServiceProvider

---

## 📄 License

The MIT License (MIT). Please see [License File](LICENSE) for more information.

---

## 🤝 Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

---

## 📞 Support

If you discover any security vulnerabilities or bugs, please send an e-mail via the contact information provided in the package.

---

**CQRS Flow** - Clean architecture for Laravel applications 🚀
