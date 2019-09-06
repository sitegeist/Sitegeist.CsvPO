<?php
namespace Neos\Flow\Persistence\Doctrine\Migrations;

use Doctrine\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs! This block will be used as the migration description if getDescription() is not used.
 */
class Version20190905142049 extends AbstractMigration
{

    /**
     * @return string
     */
    public function getDescription()
    {
        return '';
    }

    /**
     * @param Schema $schema
     * @return void
     */
    public function up(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on "mysql".');
        $this->addSql('DROP INDEX UNIQ_EF1075AE1D71B2699704492C9095C972 ON sitegeist_csvpo_domain_translationoverride');
        $this->addSql('DROP INDEX sourceIdentifier_index ON sitegeist_csvpo_domain_translationoverride');
        $this->addSql('ALTER TABLE sitegeist_csvpo_domain_translationoverride CHANGE translationidentifier labelidentifier VARCHAR(255) NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_EF1075AE1D71B2699EA319819095C972 ON sitegeist_csvpo_domain_translationoverride (sourceIdentifier, labelIdentifier, localeIdentifier)');
        $this->addSql('CREATE INDEX sourceIdentifier_index ON sitegeist_csvpo_domain_translationoverride (sourceIdentifier(255))');
    }

    /**
     * @param Schema $schema
     * @return void
     */
    public function down(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on "mysql".');
        $this->addSql('DROP INDEX UNIQ_EF1075AE1D71B2699EA319819095C972 ON sitegeist_csvpo_domain_translationoverride');
        $this->addSql('DROP INDEX sourceIdentifier_index ON sitegeist_csvpo_domain_translationoverride');
        $this->addSql('ALTER TABLE sitegeist_csvpo_domain_translationoverride CHANGE labelidentifier translationidentifier VARCHAR(255) NOT NULL COLLATE utf8mb4_unicode_ci');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_EF1075AE1D71B2699704492C9095C972 ON sitegeist_csvpo_domain_translationoverride (sourceidentifier, translationidentifier, localeidentifier)');
        $this->addSql('CREATE INDEX sourceIdentifier_index ON sitegeist_csvpo_domain_translationoverride (sourceidentifier)');
    }
}
