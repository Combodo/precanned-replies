<?php

namespace Combodo\iTop\Test\UnitTest;

use MetaModel;
use PrecannedRepliesPlugIn;

class ConfigTest extends ItopDataTestCase
{


    public function testGetConfigShouldReturnDefaultIfNoConfigAvailable()
    {
        $this->GivenModuleConfig([]);

        $oService = new PrecannedRepliesPlugIn();

        $this->AssertArraysHaveSameItems([
            'UserRequest' => ['public_log'],
        ], $oService->GetConfig());
    }

    public function testGetConfigShouldTransformLegacyIntoNewFormat()
    {
        $this->GivenModuleConfig([
            'target_class' => 'Incident',
            'target_caselog' => 'private_log',
        ]);

        $oService = new PrecannedRepliesPlugIn();

        $this->AssertArraysHaveSameItems([
            'Incident' => ['private_log'],
        ], $oService->GetConfig());

    }

    public function testGetConfigShouldMergeLegacyIntoNewFormat()
    {
        $this->GivenModuleConfig([
            'target_class' => 'UserRequest',
            'target_caselog' => 'public_log',
            'targets' => [
                'Ticket' => 'private_log',
            ]
        ]);

        $oService = new PrecannedRepliesPlugIn();

        $this->AssertArraysHaveSameItems([
            'UserRequest' => ['public_log'],
            'Ticket' => ['private_log'],
        ], $oService->GetConfig());
    }

    private function GivenModuleConfig(array $aModuleConfig)
    {
        $oConfig = MetaModel::GetConfig();
        $aModuleSettings = $this->GetNonPublicProperty($oConfig, 'm_aModuleSettings');
        unset($aModuleSettings['precanned-replies']);
        $this->SetNonPublicProperty($oConfig, 'm_aModuleSettings', $aModuleSettings);

        foreach ($aModuleConfig as $sKey => $sValue) {
            $oConfig->SetModuleSetting('precanned-replies', $sKey, $sValue);
        }
    }

}
