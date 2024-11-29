<?php

namespace Combodo\iTop\Test\UnitTest;

use MetaModel;
use PrecannedRepliesPlugIn;

class ConfigTest extends ItopDataTestCase
{
    public function testGetConfig_ShouldReturnDefaultIfNoConfigAvailable()
    {
        $this->GivenModuleConfig([]);
        $oService = new PrecannedRepliesPlugIn();
        // var_export($oService->GetConfig());
        $this->assertEquals([ 'UserRequest' => 'public_log',], $oService->GetConfig(), "The default precanned-replies config should be returned if no config is available");
    }
    public function testGetConfig_ShouldTransformLegacyIntoNewFormat()
    {
        $this->GivenModuleConfig([
            'target_class' => 'Incident',
            'target_caselog' => 'private_log',
        ]);
        $oService = new PrecannedRepliesPlugIn();
        // var_export($oService->GetConfig());
        $this->assertEquals(['Incident' => 'private_log',],
            $oService->GetConfig(),
            "The legacy precanned-replies config should be transformed into the new format");
    }
    public function testGetConfig_ShouldMergeLegacyIntoNewFormat()
    {
        $this->GivenModuleConfig([
            'target_class' => 'UserRequest',
            'target_caselog' => 'public_log',
            'targets' => [
                'Ticket' => 'private_log',
            ]
        ]);
        $oService = new PrecannedRepliesPlugIn();
        // var_export($oService->GetConfig());
        $this->assertEquals([
            'UserRequest' => 'public_log',
            'Ticket' => 'private_log',
            ], $oService->GetConfig(),
            "The legacy precanned-replies config should be merged into the new format"
        );
    }
    public function testGetConfig_ShouldIgnoreLegacyClassIfExistsAlsoInNewFormat()
    {
        $this->GivenModuleConfig([
            'target_class' => 'UserRequest',
            'target_caselog' => 'public_log',
            'targets' => [
                'UserRequest' => 'private_log',
            ]
        ]);
        $oService = new PrecannedRepliesPlugIn();
        // var_export($oService->GetConfig());
        $this->assertEquals([
            'UserRequest' => 'private_log',
        ], $oService->GetConfig(),
            "The legacy precanned-replies config should be ignored if the class is also in the new format"
        );
    }
    public function testGetLogAttCodes_ForNotConfiguredClass()
    {
        $this->GivenModuleConfig([
            'targets' => [
                'UserRequest' => 'public_log',
                'Ticket' => 'private_log',
            ]
        ]);
        $oService = new PrecannedRepliesPlugIn();
        $oObject = MetaModel::NewObject('Person');
        $this->assertEquals([ ], $oService->GetLogAttCodes($oObject), "Empty array should be returned if the class is not configured for precanned-replies");
    }
    public function testGetLogAttCodes_ForChildClass()
    {
        $this->GivenModuleConfig([
            'targets' => [
                'Ticket' => 'private_log',
            ]
        ]);
        $oService = new PrecannedRepliesPlugIn();
        $oObject = MetaModel::NewObject('Change');
        $this->assertEquals(['private_log'], $oService->GetLogAttCodes($oObject), "Parent class precanned-replies logs should be inherited by child classes");
    }
    public function testGetLogAttCodes_ParentAndChildClass()
    {
        $this->GivenModuleConfig([
            'targets' => [
                'UserRequest' => 'public_log',
                'Ticket' => 'private_log',
            ]
        ]);
        $oService = new PrecannedRepliesPlugIn();
        $oObject = MetaModel::NewObject('UserRequest');
        $this->assertEquals(['public_log','private_log'], $oService->GetLogAttCodes($oObject), "Parent class precanned-replies logs should be merged with child classes");
    }
    public function testGetLogAttCodes_InvalidLogShouldBeFiltered()
    {
        $this->GivenModuleConfig([
            'targets' => [
                'UserRequest' => 'agent_id',
            ]
        ]);
        $oService = new PrecannedRepliesPlugIn();
        $oObject = MetaModel::NewObject('UserRequest');
        $this->assertEquals([  ], $oService->GetLogAttCodes($oObject),"Invalid log in precanned-replies configuration should be filtered out");
    }

    public function testGetLogAttCodes_MultipleLogsOnSameClassWithSomeInvalid()
    {
        $this->GivenModuleConfig([
            'targets' => [
                'UserRequest' => 'public_log,private_log,invalid_log',
            ]
        ]);
        $oService = new PrecannedRepliesPlugIn();
        $oObject = MetaModel::NewObject('UserRequest');
        $this->assertEquals([ 'public_log','private_log' ], $oService->GetLogAttCodes($oObject),"Multiple log in precanned-replies configuration should be split and invalid should be filtered out");
    }

    public function testGetLogAttCodes_CaselogWithBlanks()
    {
        $this->GivenModuleConfig([
            'targets' => [
                'UserRequest' => ' public_log , private_log',
            ]
        ]);
        $oService = new PrecannedRepliesPlugIn();
        $oObject = MetaModel::NewObject('UserRequest');
        var_export($oService->GetLogAttCodes($oObject));
        $this->assertEquals([ 'public_log','private_log' ], $oService->GetLogAttCodes($oObject),"Multiple log in precanned-replies configuration should be split and invalid should be filtered out");
    }
    public function testGetLogAttCodes_WrongCasseNotSupported()
    {
        $this->GivenModuleConfig([
            'targets' => [
                'UserRequest' => ' PUBLIC_LOG , private_log',
            ]
        ]);
        $oService = new PrecannedRepliesPlugIn();
        $oObject = MetaModel::NewObject('UserRequest');
        var_export($oService->GetLogAttCodes($oObject));
        $this->assertEquals([ 'private_log' ], $oService->GetLogAttCodes($oObject),"Multiple log in precanned-replies configuration should be split and invalid should be filtered out");
    }

    // HELPERS

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
