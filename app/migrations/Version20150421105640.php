<?php

namespace Rialto\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Drop UUID from StockItemFeature and use featureCode instead.
 *
 * Backfill any missing feature codes.
 */
class Version20150421105640 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $map = [
            '19f95de7-e3bd-11e4-aa9c-06d10c61e76a' => 'processor_cores',
            '263858cb-e3a0-11e4-aa9c-06d10c61e76a' => 'lcd_manufacturer',
            '59517123-e3a0-11e4-aa9c-06d10c61e76a' => 'product_family',
            '629323fd-929e-11e4-aa9c-06d10c61e76a' => 'buzzer',
            '7505ba29-e3b4-11e4-aa9c-06d10c61e76a' => 'audio',
            '92023f21-d1af-11e4-aa9c-06d10c61e76a' => 'access_point_mode',
            '92c8a0a2-e3b7-11e4-aa9c-06d10c61e76a' => 'serial_port',
            '9a040ed6-e3c2-11e4-aa9c-06d10c61e76a' => 'dimensions_width',
            'cb7bdd82-4012-11e4-9bc6-80497111d2e2' => 'dimensions_width',
            'bbba442a-e3b4-11e4-aa9c-06d10c61e76a' => 'processor_max_clock',
            'bdcbd822-e3c2-11e4-aa9c-06d10c61e76a' => 'dimensions_length',
            'cb7bb656-4012-11e4-9bc6-80497111d2e2' => 'dimensions_length',
            'cb7b69c9-4012-11e4-9bc6-80497111d2e2' => '24pin_connector',
            'cb7b6ade-4012-11e4-9bc6-80497111d2e2' => '24pin_header',
            'cb7b70a6-4012-11e4-9bc6-80497111d2e2' => '40pin_header',
            'cb7b764d-4012-11e4-9bc6-80497111d2e2' => '4pin_header',
            'cb7b773a-4012-11e4-9bc6-80497111d2e2' => '5v_ad_convertors',
            'cb7b7910-4012-11e4-9bc6-80497111d2e2' => '60pin_connector',
            'cb7b79fa-4012-11e4-9bc6-80497111d2e2' => '60pin_header',
            'cb7b7ae8-4012-11e4-9bc6-80497111d2e2' => '6pin_header',
            'cb7b7bd1-4012-11e4-9bc6-80497111d2e2' => '75v_ad_convertors',
            'cb7b7cb6-4012-11e4-9bc6-80497111d2e2' => 'wifi',
            'cb7b7f80-4012-11e4-9bc6-80497111d2e2' => '80pin_connector',
            'cb7b8063-4012-11e4-9bc6-80497111d2e2' => 'accelerometer',
            'cb7b822e-4012-11e4-9bc6-80497111d2e2' => 'processor_architecture',
            'cb7b8312-4012-11e4-9bc6-80497111d2e2' => 'barometer',
            'cb7b83fa-4012-11e4-9bc6-80497111d2e2' => 'battery_connector',
            'cb7b84e8-4012-11e4-9bc6-80497111d2e2' => 'bluetooth',
            'cb7b86c2-4012-11e4-9bc6-80497111d2e2' => 'camera',
            'cb7b8b4e-4012-11e4-9bc6-80497111d2e2' => 'compatibility',
            'cb7b9289-4012-11e4-9bc6-80497111d2e2' => 'board_to_board_connectors',
            'cb7b9371-4012-11e4-9bc6-80497111d2e2' => 'connect_to_irobot_create',
            'cb7b9540-4012-11e4-9bc6-80497111d2e2' => 'controller_area_network_can',
            'cb7b962a-4012-11e4-9bc6-80497111d2e2' => 'graphics_dsp',
            'cb7b99cd-4012-11e4-9bc6-80497111d2e2' => 'dvid',
            'cb7b9e5e-4012-11e4-9bc6-80497111d2e2' => 'ethernet',
            'cb7ba5a0-4012-11e4-9bc6-80497111d2e2' => 'gps',
            'cb7ba688-4012-11e4-9bc6-80497111d2e2' => 'graphics_acceleration',
            'cb7ba770-4012-11e4-9bc6-80497111d2e2' => 'gyroscope',
            'cb7ba856-4012-11e4-9bc6-80497111d2e2' => 'hdmi',
            'cb7bab13-4012-11e4-9bc6-80497111d2e2' => 'hubcommander_interface',
            'cb7badc3-4012-11e4-9bc6-80497111d2e2' => 'io_header',
            'cb7baead-4012-11e4-9bc6-80497111d2e2' => 'io_receptacle_pins',
            'cb7baf8e-4012-11e4-9bc6-80497111d2e2' => 'jtag_connector',
            'cb7bb077-4012-11e4-9bc6-80497111d2e2' => 'layout',
            'cb7bb159-4012-11e4-9bc6-80497111d2e2' => 'lcd',
            'cb7bba06-4012-11e4-9bc6-80497111d2e2' => 'mmcsd_slot',
            'cb7bbaec-4012-11e4-9bc6-80497111d2e2' => 'motor_control_gpiocani2cspiuar',
            'cb7bbbd3-4012-11e4-9bc6-80497111d2e2' => 'flash_nand',
            'cb7bbd9e-4012-11e4-9bc6-80497111d2e2' => 'flash_nor',
            'cb7bc054-4012-11e4-9bc6-80497111d2e2' => 'performance',
            'cb7bc13b-4012-11e4-9bc6-80497111d2e2' => 'power',
            'cb7bc21f-4012-11e4-9bc6-80497111d2e2' => 'power_management',
            'cb7bc2fe-4012-11e4-9bc6-80497111d2e2' => 'processor',
            'cb7bc3dc-4012-11e4-9bc6-80497111d2e2' => 'processor_base_clock',
            'cb7bc4b8-4012-11e4-9bc6-80497111d2e2' => 'pwm',
            'cb7bc681-4012-11e4-9bc6-80497111d2e2' => 'ram',
            'cb7bc852-4012-11e4-9bc6-80497111d2e2' => 'rs232_serial_ports',
            'cb7bc937-4012-11e4-9bc6-80497111d2e2' => 'rtc_battery_holder',
            'cb7bcb0e-4012-11e4-9bc6-80497111d2e2' => 'lcd_size',
            'cb7bcfe5-4012-11e4-9bc6-80497111d2e2' => 'specification',
            'cb7bd0d6-4012-11e4-9bc6-80497111d2e2' => 'standards',
            'cb7bd2a0-4012-11e4-9bc6-80497111d2e2' => 'temperature',
            'cb7bd470-4012-11e4-9bc6-80497111d2e2' => 'lcd_touchscreen',
            'cb7bd63f-4012-11e4-9bc6-80497111d2e2' => 'usb_console_port',
            'cb7bd726-4012-11e4-9bc6-80497111d2e2' => 'usb_device',
            'cb7bd809-4012-11e4-9bc6-80497111d2e2' => 'usb_host',
            'cb7bd8ed-4012-11e4-9bc6-80497111d2e2' => 'usb_otg',
            'cb7bd9d5-4012-11e4-9bc6-80497111d2e2' => 'usb_powered',
            'cb7bdc93-4012-11e4-9bc6-80497111d2e2' => 'weight',
            'd00270cc-e3c5-11e4-aa9c-06d10c61e76a' => 'storage_expansion',
            'd0f24e9d-e3c2-11e4-aa9c-06d10c61e76a' => 'dimensions_height',
            'cb7baa2b-4012-11e4-9bc6-80497111d2e2' => 'dimensions_height',
            'f56a0b03-e3c4-11e4-aa9c-06d10c61e76a' => 'magnetometer',
            'cb7b8a65-4012-11e4-9bc6-80497111d2e2' => 'mounting_holes_com',
            'cb7b8983-4012-11e4-9bc6-80497111d2e2' => 'mounting_holes_expansion',
        ];

        foreach ($map as $id => $code) {
            $this->addSql("UPDATE StockItemFeature SET featureCode = :code WHERE featureCode = '' AND featureId = :id", [
                'code' => $code,
                'id' => $id,
            ]);
        }

        $this->addSql("delete from StockItemFeature where featureCode = ''");

        $this->addSql('ALTER TABLE StockItemFeature DROP PRIMARY KEY');
        $this->addSql('ALTER TABLE StockItemFeature DROP FOREIGN KEY FK_F3012DC8A47C422A');
        $this->addSql('ALTER TABLE StockItemFeature DROP featureId, CHANGE stockItemId stockCode varchar(20) COLLATE utf8_unicode_ci NOT NULL, CHANGE featureCode featureCode VARCHAR(30) NOT NULL');
        $this->addSql('ALTER TABLE StockItemFeature ADD PRIMARY KEY (featureCode, stockCode)');
        $this->addSql('ALTER TABLE StockItemFeature ADD CONSTRAINT FK_F3012DC8A47C422A FOREIGN KEY (stockCode) REFERENCES StockMaster (StockID) ON DELETE CASCADE');

    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->throwIrreversibleMigrationException();
    }
}
