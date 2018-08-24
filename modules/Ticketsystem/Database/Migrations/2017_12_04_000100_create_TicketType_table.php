<?php

use Illuminate\Database\Schema\Blueprint;

class CreateTicketTypeTable extends BaseMigration
{
    protected $tablename = 'tickettype';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create($this->tablename, function (Blueprint $table) {
            $this->up_table_generic($table);

            $table->string('name')->nullable();
            $table->integer('parent_id')->nullable();
            $table->string('description')->nullable();

            return parent::up();
        });

        $this->set_fim_fields(['name', 'description']);

        // Add default Ticket Types?
        // $default_tts = array(
        // 	['name' => 'General'],
        // 	['name' => 'Technical'],
        // 	['name' => 'Accounting'],
        // 	);

        // foreach ($default_tts as $data)
        // 	\Modules\Ticketsystem\Entities\TicketType::create($data);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop($this->tablename);
    }
}
