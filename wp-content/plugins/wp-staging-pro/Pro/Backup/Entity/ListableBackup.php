<?php

namespace WPStaging\Pro\Backup\Entity;

class ListableBackup
{
    /** @var string */
    public $backupName;

    /** @var int A timestamp of the date this backup was created */
    public $dateCreatedTimestamp;

    /** @var int A formatted $dateCreatedTimestamp */
    public $dateCreatedFormatted;

    /** @var string */
    public $downloadUrl;

    /** @var string */
    public $fullPath;

    /** @var string The basename of the backup encrypted as md5 */
    public $md5BaseName;

    /** @var string */
    public $id;

    /** @var bool */
    public $isExportingDatabase = false;

    /** @var bool */
    public $isExportingMuPlugins = false;

    /** @var bool */
    public $isExportingOtherWpContentFiles = false;

    /** @var bool */
    public $isExportingPlugins = false;

    /** @var bool */
    public $isExportingThemes = false;

    /** @var bool */
    public $isExportingUploads = false;

    /** @var string */
    public $name;

    /** @var string */
    public $notes;

    /** @var int The size of this backup in bytes */
    public $size;

    /** @var string The type of this backup */
    public $type;

    /** @var bool Whether this backup was automatically generated. (Eg: pushing staging into production) */
    public $automatedBackup = false;

    /** @var bool Whether this backup was generated from a legacy .SQL file export */
    public $legacy = false;
}
