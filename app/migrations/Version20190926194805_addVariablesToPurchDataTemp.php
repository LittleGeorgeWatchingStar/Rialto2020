<?php

namespace Rialto\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20190926194805_addVariablesToPurchDataTemp extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE PurchasingDataTemplate ADD variables LONGTEXT NOT NULL COMMENT \'(DC2Type:json_array)\'');
    }

    public function postUp(Schema $schema)
    {
        parent::postUp($schema);

        $data = $this->connection->prepare('SELECT strategyID, minimumOrderQty, manufacturerLeadTime, supplierLeadTime, unitCost FROM PurchasingCostTemplate');
        $data->execute();
        foreach ($data as $row) {
            $strategyId = $row['strategyID'];
            $minOrderQty = $row['minimumOrderQty'];
            $manLeadTime = $row['manufacturerLeadTime'];
            $supLeadTime = $row['supplierLeadTime'];
            $unitCost = $row['unitCost'];

            $this->connection->update(
                'PurchasingDataTemplate',
                [
                    'variables' => json_encode([
                        'minimumOrderQty' => $minOrderQty,
                        'manufacturerLeadTime' => $manLeadTime,
                        'supplierLeadTime' =>$supLeadTime,
                        'unitCost' => $unitCost,
                    ])
                ],
                ['id' => $strategyId]);
        }
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE PurchasingDataTemplate DROP variables');
    }
}
