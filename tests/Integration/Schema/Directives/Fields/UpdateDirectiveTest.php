<?php

namespace Tests\Integration\Schema\Directives\Fields;

use Tests\DBTestCase;
use Tests\Utils\Models\Task;
use Tests\Utils\Models\User;
use Tests\Utils\Models\Company;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UpdateDirectiveTest extends DBTestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    public function itCanUpdateFromFieldArguments()
    {
        factory(Company::class)->create(['name' => 'foo']);
        $schema = '
        type Company {
            id: ID!
            name: String!
        }
        
        type Mutation {
            updateCompany(
                id: ID!
                name: String
            ): Company @update
        }
        ';
        $query = '
        mutation {
            updateCompany(
                id: 1
                name: "bar"
            ) {
                id
                name
            }
        }
        ';
        $result = $this->execute($schema, $query);

        $this->assertSame('1', array_get($result, 'data.updateCompany.id'));
        $this->assertSame('bar', array_get($result, 'data.updateCompany.name'));
        $this->assertSame('bar', Company::first()->name);
    }
    /**
     * @test
     */
    public function itCanUpdateFromInputObject()
    {
        factory(Company::class)->create(['name' => 'foo']);
        $schema = '
        type Company {
            id: ID!
            name: String!
        }
        
        type Mutation {
            updateCompany(
                input: UpdateCompanyInput
            ): Company @update(flatten: true)
        }
        
        input UpdateCompanyInput {
            id: ID!
            name: String
        }
        ';
        $query = '
        mutation {
            updateCompany(input: {
                id: 1
                name: "bar"
            }) {
                id
                name
            }
        }
        ';
        $result = $this->execute($schema, $query);

        $this->assertSame('1', array_get($result, 'data.updateCompany.id'));
        $this->assertSame('bar', array_get($result, 'data.updateCompany.name'));
        $this->assertSame('bar', Company::first()->name);
    }

    /**
     * @test
     */
    public function itCanUpdateWithBelongsTo()
    {
        factory(User::class, 2)->create();
        factory(Task::class)->create([
            'name' => 'bar',
            'user_id' => 1,
        ]);

        $schema = '
        type Task {
            id: ID!
            name: String!
            user: User @belongsTo
        }
        
        type User {
            id: ID
        }
        
        type Mutation {
            updateTask(input: UpdateTaskInput!): Task @update(flatten: true)
        }
        
        input UpdateTaskInput {
            id: ID!
            name: String
            user: ID
        }
        ';
        $query = '
        mutation {
            updateTask(input: {
                id: 1
                name: "foo"
                user: 2
            }) {
                id
                name
                user {
                    id
                }
            }
        }
        ';
        $result = $this->execute($schema, $query);

        $this->assertSame('1', array_get($result, 'data.updateTask.id'));
        $this->assertSame('foo', array_get($result, 'data.updateTask.name'));
        $this->assertSame('2', array_get($result, 'data.updateTask.user.id'));

        $task = Task::first();
        $this->assertSame(2, $task->user_id);
        $this->assertSame('foo', $task->name);
    }
}
