<?php

namespace Database\Factories;

use App\Models\Contact;
use App\Models\Task;
use Illuminate\Database\Eloquent\Factories\Factory;

class TaskFactory extends Factory
{
    protected $model = Task::class;

    public function definition(): array
    {
        $contact = Contact::factory();

        return [
            'contact_id' => $contact,
            'taskable_id' => null,
            'taskable_type' => null,
            'title' => $this->faker->sentence(3),
            'description' => $this->faker->paragraph(),
            'assigned_to' => $this->faker->randomElement(['Leo', 'Amberle', 'Client Care']),
            'priority' => $this->faker->randomElement(['high', 'normal', 'low']),
            'status' => $this->faker->randomElement(['open', 'in_progress', 'done']),
            'due_at' => $this->faker->optional()->dateTimeBetween('now', '+7 days'),
        ];
    }

    public function configure()
    {
        return $this->afterCreating(function (Task $task): void {
            if (!$task->taskable_id) {
                $task->taskable_id = $task->contact_id;
                $task->taskable_type = Contact::class;
                $task->save();
            }
        });
    }
}
