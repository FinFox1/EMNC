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
                'unsigned' => true,
            ]);
            $table->addColumn('room_id', 'string', [
                'notnull' => true,
                'length' => 255,
            ]);
            $table->addColumn('user_id', 'string', [
                'notnull' => true,
                'length' => 64,
            ]);
            $table->addColumn('joined_at', 'integer', [
                'notnull' => true,
            ]);
            $table->addColumn('participant_type', 'integer', [
                'notnull' => true,
                'default' => 3,
            ]);
            $table->setPrimaryKey(['id']);
            $table->addUniqueIndex(['room_id', 'user_id']);
            $table->addIndex(['user_id']);
        }

        if (!$schema->hasTable('elementmatrix_user_mapping')) {
            $table = $schema->createTable('elementmatrix_user_mapping');
            $table->addColumn('id', 'bigint', [
                'autoincrement' => true,
                'notnull' => true,
                'unsigned' => true,
            ]);
            $table->addColumn('nextcloud_user_id', 'string', [
                'notnull' => true,
                'length' => 64,
            ]);
            $table->addColumn('matrix_user_id', 'string', [
                'notnull' => true,
                'length' => 255,
            ]);
            $table->addColumn('access_token', 'text', [
                'notnull' => false,
            ]);
            $table->addColumn('created_at', 'integer', [
                'notnull' => true,
            ]);
            $table->setPrimaryKey(['id']);
            $table->addUniqueIndex(['nextcloud_user_id']);
            $table->addUniqueIndex(['matrix_user_id']);
        }

        return $schema;
    }
}                              
