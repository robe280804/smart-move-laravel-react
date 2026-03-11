# Testing Rules (Laravel + PHPUnit)

The goal is to maintain:

- deterministic tests
- readable tests
- isolated tests
- fast test execution
- production-like behavior

---

# 1. Testing Philosophy

Tests must follow these principles:

1. **Test behavior, not implementation**
2. **Each test should verify one clear outcome**
3. **Tests must be deterministic**
4. **Tests must be readable without context**
5. **Tests must not depend on execution order**

Bad tests usually:

- depend on previous tests
- assert too many things
- mock too much
- test internal implementation

Good tests:

- describe behavior
- verify business rules
- run independently

---

# 2. Test Structure

Use the **Arrange / Act / Assert** pattern.

Example:

```php
public function test_user_can_create_post()
{
    // Arrange
    $user = User::factory()->create();

    // Act
    $response = $this->actingAs($user)->post('/posts', [
        'title' => 'Test Post',
        'content' => 'Content'
    ]);

    // Assert
    $response->assertStatus(201);

    $this->assertDatabaseHas('posts', [
        'title' => 'Test Post'
    ]);
}
```

```php
public function test_user_can_login()
{
$user = User::factory()->create([
'password' => bcrypt('password')
]);

    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'password'
    ]);

    $response->assertStatus(200);
}
```

--

3. Feature Tests

Located in: tests/Feature

Used for:

- HTTP endpoints
- authentication
- middleware
- full request lifecycle

Example:

```php
public function test_user_can_login()
{
$user = User::factory()->create([
'password' => bcrypt('password')
]);

    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'password'
    ]);

    $response->assertStatus(200);
}

```

4. Unit Tests

Located in: tests/Unit

Used for:

- services
- helpers
- business logic
- pure PHP logic

Example:

```php
public function test_order_total_is_calculated_correctly()
{
$calculator = new OrderCalculator();

    $total = $calculator->calculate([
        10,
        20,
        30
    ]);

    $this->assertEquals(60, $total);
}
```

---

5. Factories Must Be Used

Always create models using factories.

Good:
$user = User::factory()->create();

Bad:

$user = new User();
$user->name = "John";
$user->email = "john@email.com";
$user->save();

---

6. Mocking Rules

Mock only external dependencies, such as:

- external APIs
- queues
- mail
- events
- third-party services

---

7. Test Speed Rules

Tests must remain fast.

Avoid:

- real external API calls
- unnecessary DB operations
- sleep() calls

Use:

- Queue::fake()
- Mail::fake()
- Event::fake()

---
