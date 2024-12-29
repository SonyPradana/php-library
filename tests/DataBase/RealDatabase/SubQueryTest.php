<?php

declare(strict_types=1);

namespace System\Test\Database\RealDatabase;

use System\Database\MyQuery\InnerQuery;
use System\Database\MyQuery\Join\InnerJoin;
use System\Database\MyQuery\Select;
use System\Test\Database\BaseConnection;

use function System\Console\ok;

final class SubQueryTest extends BaseConnection
{
    // scehema

    protected function createUserSchema(): bool
    {
        return $this
           ->pdo
           ->query('CREATE TABLE users (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(255),
                email VARCHAR(255),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            );')
           ->execute();
    }

    private function createOrderSchema(): bool
    {
        return $this
           ->pdo
           ->query('CREATE TABLE orders (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT,
                total_amount DECIMAL(10, 2),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id)
            );')
           ->execute();
    }

    private function createProductSchema(): bool
    {
        return $this
           ->pdo
           ->query('CREATE TABLE products (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(255)
            );')
           ->execute();
    }

    private function createSelesSchema(): bool
    {
        return $this
           ->pdo
           ->query('CREATE TABLE sales (
                id INT AUTO_INCREMENT PRIMARY KEY,
                product_id INT,
                quantity INT,
                price DECIMAL(10, 2)
            );')
           ->execute();
    }

    private function createCustomerSchema(): bool
    {
        return $this
           ->pdo
           ->query('CREATE TABLE customers (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(255),
                city VARCHAR(255)
            );')
           ->execute();
    }

    private function createTransactionSchema(): bool
    {
        return $this
           ->pdo
           ->query('CREATE TABLE transactions (
                id INT AUTO_INCREMENT PRIMARY KEY,
                customer_id INT,
                amount DECIMAL(10, 2),
                transaction_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (customer_id) REFERENCES customers(id)
            );')
           ->execute();
    }

    // factory

    private function createUsers(): bool
    {
        return $this
           ->pdo
           ->query('INSERT INTO users (name, email) VALUES
                ("Alice", "alice@example.com"),
                ("Bob", "bob@example.com"),
                ("Charlie", "charlie@example.com")
            ;')
            ->execute();
    }

    private function createOrders(): bool
    {
        return $this
           ->pdo
           ->query('INSERT INTO orders (user_id, total_amount) VALUES
                (1, 1200),
                (2, 800),
                (3, 1500)
            ;')
            ->execute();
    }

    private function createProducts(): bool
    {
        return $this
        ->pdo
        ->query('INSERT INTO products (name) VALUES
           (\'Laptop\'), (\'Phone\'), (\'Tablet\')
        ;')
        ->execute();
    }

    private function createSeles(): bool
    {
        return $this
        ->pdo
        ->query('INSERT INTO sales (product_id, quantity, price) VALUES
            (1, 2, 1000),  -- Laptop
            (2, 3, 800),   -- Phone
            (3, 1, 600);   -- Tablet
        ')
        ->execute();
    }

    private function createCustomers(): bool
    {
        return $this
           ->pdo
           ->query('INSERT INTO customers (name, city) VALUES
                ("Alice", "New York"),
                ("Bob", "Los Angeles"),
                ("Charlie", "Chicago")
            ;')
            ->execute();
    }

    private function createTransactions(): bool
    {
        return $this
           ->pdo
           ->query('INSERT INTO transactions (customer_id, amount, transaction_date) VALUES
                (1, 600, "2024-12-01 10:00:00"),  -- Alice
                (1, 500, "2024-12-02 12:00:00"),  -- Alice
                (2, 400, "2024-12-01 11:00:00"),  -- Bob
                (3, 800, "2024-12-03 14:00:00");  -- Charlie
            ;')
            ->execute();
    }

    // setup

    protected function setUp(): void
    {
        $this->createConnection();
    }

    protected function tearDown(): void
    {
        $this->dropConnection();
    }

    // tests

    /**
     * @test
     *
     * @group database
     */
    public function itCanSelectSubQueryUsingWhere()
    {
        $this->createUserSchema();
        $this->createOrderSchema();
        $this->createUsers();
        $this->createOrders();

        $users = new Select('users', ['name', 'email'], $this->pdo);
        $users->whereIn('id', (new Select('orders', ['user_id'], $this->pdo))
            ->compare('total_amount', '>', 1000)
        );
        $users = $users->get();

        $this->assertCount(2, $users);
        $this->assertSame('Alice', $users[0]['name']);
        $this->assertSame('Charlie', $users[1]['name']);
    }

    /**
     * @test
     *
     * @group database
     */
    public function itCanSelectSubQueryUsingFrom()
    {
        $this->markTestSkipped('this test requere select with group by statment');

        $this->createUserSchema();
        $this->createOrderSchema();
        $this->createProductSchema();
        $this->createSelesSchema();
        $this->createUsers();
        $this->createOrders();
        $this->createProducts();
        $this->createSeles();

        // SELECT sub.product_id, sub.total_quantity, sub.total_sales
        // FROM (
        //     SELECT
        //         product_id,
        //         SUM(quantity) AS total_quantity,
        //         SUM(quantity * price) AS total_sales
        //     FROM sales
        //     GROUP BY product_id
        // ) AS sub;

        $products = new Select(
            new InnerQuery(
                new Select(
                    'sales',
                    ['product_id', 'SUM(quantity) AS total_quantity', 'SUM(quantity * price) AS total_sales'],
                    $this->pdo
                ),
                'sub'
            ),
            ['sub.product_id', 'sub.total_quantity', 'sub.total_sales'],
            $this->pdo
        );

        $products = $products->get();

        $this->assertCount(3, $products);
    }

    /**
     * @test
     *
     * @group database
     */
    public function itCanSelectSubQueryUsingJoin(): void
    {
        $this->markTestSkipped('this test requere select with group by statment');

        $this->createCustomerSchema();
        $this->createTransactionSchema();
        $this->createCustomers();
        $this->createTransactions();

        // SELECT c.name, sub.total_spent
        // FROM customers c
        // JOIN (
        //     SELECT customer_id, SUM(amount) AS total_spent
        //     FROM transactions
        //     GROUP BY customer_id
        // ) AS sub ON c.id = sub.customer_id
        // WHERE sub.total_spent > 500;

        $customers = new Select(
            'customers',
            ['costumer.name', 'sub.total_spent'],
            $this->pdo
        );

        $customers->join(
            InnerJoin::ref(
                new InnerQuery(
                    new Select(
                        'transactions',
                        ['customer_id', 'SUM(amount) AS total_spent'],
                        $this->pdo
                    ),
                    'sub'
                ),
                'id',
                'customer_id'
            )
        );
        $customers->compare('total_spent', '>', 500);

        ok($customers->__toString())->out();

        $customers = $customers->get();

        $this->assertCount(2, $customers);
    }
}
