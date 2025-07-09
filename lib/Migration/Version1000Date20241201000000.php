<?php
namespace OCA\ElementMatrix\Migration;

use Closure;
use OCP\DB\ISchemaWrapper;
use OCP\Migration\IOutput;
use OCP\Migration\SimpleMigrationStep;

class Version1000Date20241201000000 extends SimpleMigrationStep {
    public function changeSchema(IOutput $output, Closure $schemaClosure, array $options): ?ISchemaWrapper {
        /** @var ISchemaWrapper $schema */
        $schema = $schemaClosure();

        if (!$schema->hasTable('elementmatrix_rooms')) {
            $table = $schema->createTable('elementmatrix_rooms');
            $table->addColumn('id', 'bigint', [
                'autoincrement' => true,
                'notnull' => true,
                'unsigned' => true,
            ]);
            $table->addColumn('matrix_room_id', 'string', [
                'notnull' => true,
                'length' => 255,
            ]);
            $table->addColumn('owner_id', 'string', [
                'notnull' => true,
                'length' => 64,
            ]);
            $table->addColumn('room_name', 'string', [
                'notnull' => true,
                'length' => 255,
            ]);
            $table->addColumn('room_type', 'integer', [
                'notnull' => true,
                'default' => 2,
            ]);
            $table->addColumn('created_at', 'integer', [
                'notnull' => true,
            ]);
            $table->setPrimaryKey(['id']);
            $table->addUniqueIndex(['matrix_room_id']);
            $table->addIndex(['owner_id']);
        }

        if (!$schema->hasTable('elementmatrix_participants')) {
            $table = $schema->createTable('elementmatrix_participants');
            $table->addColumn('id', 'bigint', [
                'autoincrement' => true,
                'notnull' => true,
