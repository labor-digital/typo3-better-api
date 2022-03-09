# Changelog

All notable changes to this project will be documented in this file. See [standard-version](https://github.com/conventional-changelog/standard-version) for commit guidelines.

### [10.34.3](https://github.com/labor-digital/typo3-better-api/compare/v10.34.2...v10.34.3) (2022-03-07)


### Bug Fixes

* **PidFacet:** use strict numeric merging for pid resolution ([2596b43](https://github.com/labor-digital/typo3-better-api/commit/2596b4308ee642c3c9d09f1cd304f5095de0fd10))

### [10.34.2](https://github.com/labor-digital/typo3-better-api/compare/v10.34.1...v10.34.2) (2022-03-07)


### Bug Fixes

* **Pid:** ensure pid's with numeric keys don't vanish from config state ([4dce1f9](https://github.com/labor-digital/typo3-better-api/commit/4dce1f9d1cb604b79b0d61cb00e9c6781734568f))
* **Pid:** fix crash when numeric pid key is used while merging site-based ([7b27d03](https://github.com/labor-digital/typo3-better-api/commit/7b27d0369e098abe732183ec97678972f9d00f98))

### [10.34.1](https://github.com/labor-digital/typo3-better-api/compare/v10.34.0...v10.34.1) (2022-03-02)


### Bug Fixes

* **TcaUtil:** ensure that columnsOverrides are applied correctly again ([61be084](https://github.com/labor-digital/typo3-better-api/commit/61be084fde803d9df131267e6054a8481a8d10ba))

## [10.34.0](https://github.com/labor-digital/typo3-better-api/compare/v10.33.5...v10.34.0) (2022-02-24)


### Features

* **BetterQuery:** allow "insert" to return created uid ([c43c105](https://github.com/labor-digital/typo3-better-api/commit/c43c10591ebb4cbab9783ea4d3905e4c25081994))

### [10.33.5](https://github.com/labor-digital/typo3-better-api/compare/v10.33.4...v10.33.5) (2022-02-18)


### Bug Fixes

* **ClassOverrideStage:** use correct class for override detection ([acb3048](https://github.com/labor-digital/typo3-better-api/commit/acb3048ba909ad6e7095f1a861866215e852d30b))

### [10.33.4](https://github.com/labor-digital/typo3-better-api/compare/v10.33.3...v10.33.4) (2022-02-18)


### Bug Fixes

* remove security advisories bundle ([3143b3f](https://github.com/labor-digital/typo3-better-api/commit/3143b3f489319252231928426a47a062e2f0a9ea))
* **Rendering:** use correct SingletonInterface ([c863ecc](https://github.com/labor-digital/typo3-better-api/commit/c863ecca2df7942b20b32a2adce25c37a0c26854))

### [10.33.3](https://github.com/labor-digital/typo3-better-api/compare/v10.33.2...v10.33.3) (2022-02-11)


### Bug Fixes

* **Fal\FileInfo:** read image width and height from processed image ([a9e594b](https://github.com/labor-digital/typo3-better-api/commit/a9e594bbbd5308a2749ce9e349903db99a5115c8))

### [10.33.2](https://github.com/labor-digital/typo3-better-api/compare/v10.33.1...v10.33.2) (2022-02-03)


### Bug Fixes

* **Sql\TableOverride:** ensure $useAnyway is passed along to child methods ([af3dfdf](https://github.com/labor-digital/typo3-better-api/commit/af3dfdf4b53230988c27959e8f92d06491369a80))
* **Tca:** ensure that sql columns of group opposite fields are configured correctly ([d92bf40](https://github.com/labor-digital/typo3-better-api/commit/d92bf40caedb2f3b36e68d9abdc333c2fdde1b10))

### [10.33.1](https://github.com/labor-digital/typo3-better-api/compare/v10.33.0...v10.33.1) (2022-02-01)


### Bug Fixes

* **FieldRenderer:** use __FUNCTION__ instead of __METHOD__ for legacy bridge ([9b0ba9d](https://github.com/labor-digital/typo3-better-api/commit/9b0ba9dba05ce5bc0cf24542796a47852b1969d6))

## [10.33.0](https://github.com/labor-digital/typo3-better-api/compare/v10.32.4...v10.33.0) (2022-01-31)


### Features

* **Rendering:** allow rendering of mixed table group values in FieldRenderer ([d10180c](https://github.com/labor-digital/typo3-better-api/commit/d10180ca8ce0cd227bb4001048181a9a2c6fc673))

### [10.32.4](https://github.com/labor-digital/typo3-better-api/compare/v10.32.3...v10.32.4) (2022-01-31)


### Bug Fixes

* **Tca\MmTableOption:** ensure foreign_table for opposite fields ([648c649](https://github.com/labor-digital/typo3-better-api/commit/648c649b79e477fba5d27ddb27519cfb371af868))

### [10.32.3](https://github.com/labor-digital/typo3-better-api/compare/v10.32.2...v10.32.3) (2022-01-25)


### Bug Fixes

* **Database:** fix issue where only a single result was shown in dbgQuery ([40c0844](https://github.com/labor-digital/typo3-better-api/commit/40c0844f50e8bca2d3f7ccf4778b015de5c779a6))

### [10.32.2](https://github.com/labor-digital/typo3-better-api/compare/v10.32.1...v10.32.2) (2022-01-24)


### Bug Fixes

* **TypoContext:** ensure that the parsed body is an array ([3dcf804](https://github.com/labor-digital/typo3-better-api/commit/3dcf804cf1e95adb5446a00ae6084e361b475c5b))

### [10.32.1](https://github.com/labor-digital/typo3-better-api/compare/v10.32.0...v10.32.1) (2022-01-21)


### Bug Fixes

* **Link:** provide pageRenderer clone when building frontend links in the backend ([47c967c](https://github.com/labor-digital/typo3-better-api/commit/47c967ce5a9c84e7eecad3f0138b89efec7961bf))

## [10.32.0](https://github.com/labor-digital/typo3-better-api/compare/v10.31.0...v10.32.0) (2022-01-19)


### Features

* **Event:** add new BasicBootDoneEvent ([97537a6](https://github.com/labor-digital/typo3-better-api/commit/97537a64a141ee54619cbf2beba88f42968a4439))
* **ExtConfig:** implement usage of merge options when combining config states ([f140d68](https://github.com/labor-digital/typo3-better-api/commit/f140d68e7c667591b163c444750445ff073728e6))


### Bug Fixes

* **ExtConfig:** ensure config state consistency when TCA is loaded multiple times ([b996e6a](https://github.com/labor-digital/typo3-better-api/commit/b996e6a09ddf581dee6edaaf3319bc8f0ef81a7b))
* update neunerlei/configuration to min-version 2.7.0 ([95c998c](https://github.com/labor-digital/typo3-better-api/commit/95c998c2b0715f408e4cd74d21e445c529b9160f))
* **SiteSimulationPass:** adjust simulation detection to create a site if none exists ([ba996fe](https://github.com/labor-digital/typo3-better-api/commit/ba996feb3f589a9a51c7df390c068ba7a2f0bd0b))

## [10.31.0](https://github.com/labor-digital/typo3-better-api/compare/v10.30.5...v10.31.0) (2022-01-17)


### Features

* **Tca\Table:** implement v11 feature to reuse dynamic palettes when possible ([6e9c7d5](https://github.com/labor-digital/typo3-better-api/commit/6e9c7d5c37fc4b73c7582cdfa40daf078f2b4314))


### Bug Fixes

* **Tca\Table:** ensure dynamic palettes retain their actual id when rehydrated ([9db3841](https://github.com/labor-digital/typo3-better-api/commit/9db384161337a3324492da1faba9f255f4a0525e))
* **Tca\Type:** make generation of line break ids independent of microtime ([581b2f0](https://github.com/labor-digital/typo3-better-api/commit/581b2f0566403509f77755ce3d475887efb5b035))

### [10.30.5](https://github.com/labor-digital/typo3-better-api/compare/v10.30.4...v10.30.5) (2022-01-17)


### Bug Fixes

* **ExtConfig\Icon:** ensure configured, but not registered icon files are loaded in getFilenameForIcon ([e778018](https://github.com/labor-digital/typo3-better-api/commit/e7780180924764183299a394479c316f12e1e58a))

### [10.30.4](https://github.com/labor-digital/typo3-better-api/compare/v10.30.3...v10.30.4) (2022-01-13)


### Bug Fixes

* **FieldPreset\Relations:** add eval option for select relation fields ([b16cac2](https://github.com/labor-digital/typo3-better-api/commit/b16cac2a52cf720c24201ed1823d3e316e6a0c34))

### [10.30.3](https://github.com/labor-digital/typo3-better-api/compare/v10.30.2...v10.30.3) (2022-01-13)


### Bug Fixes

* **Tca\MmTableOption:** correctly generate MM_match_fields for mmOpposite usage ([4daccdf](https://github.com/labor-digital/typo3-better-api/commit/4daccdf1a799c9118ff2a090d570ba337365d175))
* **Tca\Tree:** fix issue when moving a field before/after a container ([54c24d2](https://github.com/labor-digital/typo3-better-api/commit/54c24d2222006554ea8a333748b439137465d809))

### [10.30.2](https://github.com/labor-digital/typo3-better-api/compare/v10.30.1...v10.30.2) (2021-12-22)


### Bug Fixes

* **Links:** don't fail with a type-error when link arguments are malformed ([830348d](https://github.com/labor-digital/typo3-better-api/commit/830348da8365db5aca1af6de0430f1545fbb50b6))

### [10.30.1](https://github.com/labor-digital/typo3-better-api/compare/v10.30.0...v10.30.1) (2021-12-20)


### Bug Fixes

* **Override\ExtendedBackendUtility:** don't emit BackendUtilityRecordFilterEvent if user has no access to the table ([b0be0ae](https://github.com/labor-digital/typo3-better-api/commit/b0be0aef5f82d07d77a934ee87e8a17ea1d077d9))

## [10.30.0](https://github.com/labor-digital/typo3-better-api/compare/v10.29.1...v10.30.0) (2021-12-20)


### Features

* integrate organization units into page tree ([5ed5740](https://github.com/labor-digital/typo3-better-api/commit/5ed5740ab43d8e899559791330cadd42f17e3eba))
* **Event:** implement PageLayoutHeaderRenderingEvent ([727d1e3](https://github.com/labor-digital/typo3-better-api/commit/727d1e3fb01d2d860cf309c19098248189a003a3))
* **ExtConfig\Icon:** implement "pages"."module" TCA icon registration ([ff0ec68](https://github.com/labor-digital/typo3-better-api/commit/ff0ec68ddca39545c90adfba9cb174b4e71c7349))
* **Rendering\BackendRenderingService:** implement renderRecordTitle method ([de92c79](https://github.com/labor-digital/typo3-better-api/commit/de92c7962a159b2165c2447791e27efb6761e1c5))
* **Tca\Table:** allow table icon registration through icon identifier ([9bddf7e](https://github.com/labor-digital/typo3-better-api/commit/9bddf7e18fbda234a86731b9f88a16cbe2bd5c19))


### Bug Fixes

* **Rendering\TemplateRenderingService:** ensure that mustache helpers allow string variables ([e12141b](https://github.com/labor-digital/typo3-better-api/commit/e12141b12b8dc459c22f3070e3c7df460c8d368a))
* **Rendering\TemplateRenderingService:** fix a typo in the documentation ([e3c216e](https://github.com/labor-digital/typo3-better-api/commit/e3c216e3a1874558a9546d52f2e868af5f3b545a))

### [10.29.1](https://github.com/labor-digital/typo3-better-api/compare/v10.29.0...v10.29.1) (2021-12-16)


### Bug Fixes

* **Event:** execute FormFilterEvent on tcaSelectTreeAjaxFieldData as well ([cafb2c9](https://github.com/labor-digital/typo3-better-api/commit/cafb2c95fd23ac68d4959da96be92810213dfcf4))
* **Tca\LimitToPidsOption:** set limitToPidsRecursive to false by default ([726b5a1](https://github.com/labor-digital/typo3-better-api/commit/726b5a1c4725cde5c3fe469a92370fd952cb0972))

## [10.29.0](https://github.com/labor-digital/typo3-better-api/compare/v10.28.3...v10.29.0) (2021-12-16)


### Features

* **FieldOption\LimitToPidsOption:** add support for recursive pid resolution ([658f867](https://github.com/labor-digital/typo3-better-api/commit/658f867a7224450e0ef97f7a3b53fcfe77ef6557))
* **FieldPreset\Relations:** update applyRelationSelect to use the new options api ([6416689](https://github.com/labor-digital/typo3-better-api/commit/641668951a638d0630101463a3a68054ab876b50))
* **FormEngine\Addon:** allow recursive resolution of pids in where clauses ([a750a35](https://github.com/labor-digital/typo3-better-api/commit/a750a352df82afe6ad9fc49932bc73849f7f8d8a))
* **Tca\SelectItemsOption:** allow custom option name ([1d6bbc0](https://github.com/labor-digital/typo3-better-api/commit/1d6bbc0dcbd7ad6cd8d315505681c0e2029ad4e2))


### Bug Fixes

* **Di\ServiceProvider:** ensure that the correct package path is generated ([4fdc25e](https://github.com/labor-digital/typo3-better-api/commit/4fdc25e3983c73ab9e7f986129213bfbe91da717))
* **FieldOption\MinMaxItemOption:** add "required" definition if it not exists ([9ddc39d](https://github.com/labor-digital/typo3-better-api/commit/9ddc39d81b35dd93aa317d9eb4c624e2648cc69b))
* **FieldOption\SelectItemsOption:** ensure that applyConfig utilizes the "optionName" parameter ([7d8600d](https://github.com/labor-digital/typo3-better-api/commit/7d8600dffd537bd33da2804015bf937393f8c410))
* **FieldPreset\Basics:** add notices for deprecated options ([8dd79cd](https://github.com/labor-digital/typo3-better-api/commit/8dd79cde6b03fb667727e95baf357ef8ff61d952))

### [10.28.3](https://github.com/labor-digital/typo3-better-api/compare/v10.28.2...v10.28.3) (2021-12-16)


### Bug Fixes

* ensure the backend can resolve the correct translator when symfony container exists ([727c5b3](https://github.com/labor-digital/typo3-better-api/commit/727c5b3115389dd890c08f85b3c206e0955761b8))

### [10.28.2](https://github.com/labor-digital/typo3-better-api/compare/v10.28.1...v10.28.2) (2021-12-16)


### Bug Fixes

* ensure we don't break the Extension Configuration module of the install tool ([a78bbdd](https://github.com/labor-digital/typo3-better-api/commit/a78bbddbd08f35b8f4ad9aca4c96b8c1d1212a7e))

### [10.28.1](https://github.com/labor-digital/typo3-better-api/compare/v10.28.0...v10.28.1) (2021-12-10)

## [10.28.0](https://github.com/labor-digital/typo3-better-api/compare/v10.27.0...v10.28.0) (2021-12-10)


### Features

* **PidFacet:** extract current pid resolution into a utility class ([cdd82b6](https://github.com/labor-digital/typo3-better-api/commit/cdd82b6d61fc5fad1906fd63210cec7332421f1b))


### Bug Fixes

* **Tca\Builder:** define not existing keys as __UNSET in generated columnOverrides ([ff0fbe8](https://github.com/labor-digital/typo3-better-api/commit/ff0fbe87ea74f39dfc8822bc4e2038fa5621de6c))
* **Tca\Builder:** don't __UNSET "overrideChildTca.types" ([6e52f1e](https://github.com/labor-digital/typo3-better-api/commit/6e52f1e05e29645b8a75577c530173897517cbf6))
* **Tca\TcaUtil:** use ArrayUtility::mergeRecursiveWithOverrule for columnOverride application ([68b5758](https://github.com/labor-digital/typo3-better-api/commit/68b575895bd737a40d264455af8cfd3c35c63139))

## [10.27.0](https://github.com/labor-digital/typo3-better-api/compare/v10.26.0...v10.27.0) (2021-12-10)


### Features

* **DataHandler:** implement "soft" forcing without using _t3ba_adminUser_ ([11e2317](https://github.com/labor-digital/typo3-better-api/commit/11e231734bc183eab0c2c5a810b02c09a66a4ae3))
* **PageService:** allow "soft" forcing for dataHandler actions ([f37e587](https://github.com/labor-digital/typo3-better-api/commit/f37e5871e3f6d3168b077e51e06e9490ef77d224))
* **Tca\ContentType:** use "soft" forcing for dataHandler actions ([036f58f](https://github.com/labor-digital/typo3-better-api/commit/036f58f07ea6dbf54f27332f61cf77e270e344b1))


### Bug Fixes

* **Event\SaveEventAdapter:** execute SaveAfterDbOperationsEvent for "new" records correctly again ([904d8cf](https://github.com/labor-digital/typo3-better-api/commit/904d8cf574a65da364c268498130f88e861feff4))
* **Tca\ContentType:** fix creating zombie rows in extension table ([a796494](https://github.com/labor-digital/typo3-better-api/commit/a7964944a77930d0118c9316e7a55ae3c959e8dd))
* **Tca\ContentType:** prevent extension tables from showing up in be_groups ([6526461](https://github.com/labor-digital/typo3-better-api/commit/652646171a511fa295f4b834223d810e02f9ac70))

## [10.26.0](https://github.com/labor-digital/typo3-better-api/compare/v10.25.0...v10.26.0) (2021-12-09)


### Features

* **TypoScript:** add shortcut method to apply lib.parseFunc to a text ([a99f693](https://github.com/labor-digital/typo3-better-api/commit/a99f693a6478af2002801e0403022ed1ffd8a533))


### Bug Fixes

* **Core\CodeGeneration:** enhance detection of static references ([4934c86](https://github.com/labor-digital/typo3-better-api/commit/4934c86dfd83f5e9dd529cec0908a5d173074b56))
* **Page\ExtendedRootLineUtility:** ensure additional fields are resolved correctly ([fbebf27](https://github.com/labor-digital/typo3-better-api/commit/fbebf27b88f8dee36f7aef59557e58ca6c83cf58))
* **Tca\Builder:** ensure that palette labels are handled correctly ([d0fdb06](https://github.com/labor-digital/typo3-better-api/commit/d0fdb06719c19b9b6767d368ef877bd1c631ecab))

## [10.25.0](https://github.com/labor-digital/typo3-better-api/compare/v10.24.1...v10.25.0) (2021-12-07)


### Features

* **BackendRenderingService:** implement facade methods to FieldRenderer ([ea8f010](https://github.com/labor-digital/typo3-better-api/commit/ea8f010dcec0cc68653fcdead6c5cdb7ddeb08ea))
* **Tca\Inline:** implement new appearance options for the "inline" preset ([8fd838b](https://github.com/labor-digital/typo3-better-api/commit/8fd838b2c911897b4f67f46553f23d5946698893))


### Bug Fixes

* **Event\SaveEventAdapter:** extract the correct field array for afterDatabaseOperations hook ([3573a82](https://github.com/labor-digital/typo3-better-api/commit/3573a8260c3abaf7c3c011ef02aa36edea9850c0))
* **Event\SaveEventAdapter:** extract the correct field list for afterDatabaseOperations hook ([9c9a018](https://github.com/labor-digital/typo3-better-api/commit/9c9a0189a13617eb1a16aa7b374118a6f1c33da9))
* **RecordDataHandler:** ensure that the result of save is an integer ([a96ad50](https://github.com/labor-digital/typo3-better-api/commit/a96ad5087b5576c8a027e39ac362f32d1c4bc428))
* **RecordDataHandler:** generate "NEW_" uid through StringUtility ([3da36eb](https://github.com/labor-digital/typo3-better-api/commit/3da36eb17916b6e616a7c41a7fc1fd0731786818))
* **Renderer\FieldRenderer:** ensure to resolve the table name in all methods ([10337a3](https://github.com/labor-digital/typo3-better-api/commit/10337a375bc1746f0f19d62291bd6cf27bf6a451))

### [10.24.1](https://github.com/labor-digital/typo3-better-api/compare/v10.24.0...v10.24.1) (2021-12-07)

## [10.24.0](https://github.com/labor-digital/typo3-better-api/compare/v10.23.1...v10.24.0) (2021-12-04)


### Features

* (Core\PackageManagerAdapter): deprecate the class as it is no longer required ([c5740e7](https://github.com/labor-digital/typo3-better-api/commit/c5740e743ba51c75b15b36bcbe9a226b19664f73))


### Bug Fixes

* ensure that content-types are processed BEFORE the TCA overrides ([a7351dd](https://github.com/labor-digital/typo3-better-api/commit/a7351dd1183c95fa9dc680476c1ea446bfec8ffa))

### [10.23.1](https://github.com/labor-digital/typo3-better-api/compare/v10.23.0...v10.23.1) (2021-12-03)


### Bug Fixes

* **Di\CommonServices:** ensure interfaces can be resolved if T3 uses Failsafe container ([978d35c](https://github.com/labor-digital/typo3-better-api/commit/978d35cb94d979b610bf7d58c97bf7351ef55795))

## [10.23.0](https://github.com/labor-digital/typo3-better-api/compare/v10.22.0...v10.23.0) (2021-11-30)


### Features

* **BetterQuery:** implement better extBase support and recursive lookup for withPid ([cadc0d2](https://github.com/labor-digital/typo3-better-api/commit/cadc0d204c09f485dc4419d9faeea06e95bdefeb))
* **Core\BootStage:** replace hook package and use t3ba as package itself ([13be13e](https://github.com/labor-digital/typo3-better-api/commit/13be13eb6c8f99ca9b65893eb9f84fbf9620b16b))
* **Core\BootStage:** update dbg state for the new class based api ([b44ef22](https://github.com/labor-digital/typo3-better-api/commit/b44ef224381d5a68ef469af14e12f6d948c6e729))
* **ExceptionHandler:** don't die if no "defaultExceptionhandler" was set ([0e86cfb](https://github.com/labor-digital/typo3-better-api/commit/0e86cfb32d3797bc1d9ad1c9d87fdd163f58d782))
* **Kernel:** ensure EventBusInterface can be required through the delegate container ([488f4c9](https://github.com/labor-digital/typo3-better-api/commit/488f4c9eeb2ffe5f8b6633fd7d052af6a7c4f3b3))
* **TypoContext\Config:** add getExtBaseConfigManager method ([bc6d3f1](https://github.com/labor-digital/typo3-better-api/commit/bc6d3f143d8f0fc24360190a872c4e9daa5acdc2))


### Bug Fixes

* **Core\BootStage:** inject delegate container into general utility again in onDiContainerBeingBuild ([33286c3](https://github.com/labor-digital/typo3-better-api/commit/33286c32aa1735ce20a694b84ace0f92d247480d))
* **ExtConfig:** don't load extensions from the "sysext" directory ([7a300ff](https://github.com/labor-digital/typo3-better-api/commit/7a300ff199f2e7c62b81a4e7d20e034f2bfdc62b))

## [10.22.0](https://github.com/labor-digital/typo3-better-api/compare/v10.21.0...v10.22.0) (2021-11-29)


### Features

* **Di\CommonServices:** add "setInstance" to Common Services object ([e38322b](https://github.com/labor-digital/typo3-better-api/commit/e38322bbaf7fe1e3324a4c2b52eacc85d101784b))
* **Di\ContainerAwareTrait:** deprecate "setService" and company ([366e1f2](https://github.com/labor-digital/typo3-better-api/commit/366e1f286bf1f7e5162a16b92e3025aca8c3b81e))
* **Di\ContainerAwareTrait:** ensure makeInstance() resolves locally set services ([b5daf60](https://github.com/labor-digital/typo3-better-api/commit/b5daf60f322e41b6e518af486e5d7027870c3975))


### Bug Fixes

* harden dependencies ([a871d40](https://github.com/labor-digital/typo3-better-api/commit/a871d406083e5a067650a4306235e7a24ea9602f))
* **BootStage\DiConfigurationStage:** inject delegate container into GeneralUtility ([9bfe7d2](https://github.com/labor-digital/typo3-better-api/commit/9bfe7d285cb167d5e2330f771987683821583e4f))
* **Di\CommonService:** harden class against misuse ([8617acf](https://github.com/labor-digital/typo3-better-api/commit/8617acff34cd7cfb7fb383cd1daecfa3cf7a4066))
* **Di\ContainerAwareTrait:** set ContainerInterface as return type of getContainer() ([6a2b54e](https://github.com/labor-digital/typo3-better-api/commit/6a2b54e5c99c23ea3ee771b41b0b3c503a12d9bd))
* **Di\DelegateContainer:** harden container against errors ([8d47744](https://github.com/labor-digital/typo3-better-api/commit/8d47744e79ca67e061d21f333952735f9f23940d))
* **Tca\Builder:** fix multiple minor issues that I found while testing ([cb8623a](https://github.com/labor-digital/typo3-better-api/commit/cb8623a2d8e056e771a227c4de0450ae45540192))
* **Tca\TcaUtil:** validate that "columns" exists in applyColumnOverrides ([adda7d9](https://github.com/labor-digital/typo3-better-api/commit/adda7d9b96678d93e8b00e05a63cc30e58a9f589))
* **TypoContext:** resolve "Context" through makeInstance instead of PSR container ([80699cc](https://github.com/labor-digital/typo3-better-api/commit/80699cc95b7a11ed84730a154819b7c10fdffcd6))

## [10.21.0](https://github.com/labor-digital/typo3-better-api/compare/v10.20.0...v10.21.0) (2021-11-26)


### Features

* **ClassOverrideGenerator:** special class loading when running phpunit ([0f09ce9](https://github.com/labor-digital/typo3-better-api/commit/0f09ce9e9c66c7e6f1d56d66470398086a007d51))
* **Core\HookPackage:** special handling for unit-tests when registering our hook package ([a8c0a46](https://github.com/labor-digital/typo3-better-api/commit/a8c0a4647e4517fc6a65609cc3b4bee0de1179be))


### Bug Fixes

* **Tca\DisplayConditionBuilder:** harden display condition generation and add test ([6b08fb8](https://github.com/labor-digital/typo3-better-api/commit/6b08fb83f830a314c756a9aa1489a113a6e8633f))

## [10.20.0](https://github.com/labor-digital/typo3-better-api/compare/v10.19.1...v10.20.0) (2021-11-25)


### Features

* **Event:** add ExpressionLanguageRegistrationEvent ([6b59f6c](https://github.com/labor-digital/typo3-better-api/commit/6b59f6cdcecb88780933251943506ef4719c373c))
* **ExtConfig:** allow v11 feature to load side-based extConfig along-side default ext-config ([6c38c50](https://github.com/labor-digital/typo3-better-api/commit/6c38c500157b3b1717da009827968081ab7f9b9c))
* **ExtConfig:** introduce events to filter the extConfig state before caching it ([587bc46](https://github.com/labor-digital/typo3-better-api/commit/587bc46f2ce696dcf7ec84664d981269f12fc5f5))
* **ExtConfig:** make ConfigState persistable ([90858ea](https://github.com/labor-digital/typo3-better-api/commit/90858eabe82a3f6de8dcc6d3e3acc3d8dec1c85a))
* **ExtConfig:** make SiteBasedExtConfigGeneratedEvent v11 feature aware ([e1838f1](https://github.com/labor-digital/typo3-better-api/commit/e1838f1ee5ea38d3dd08ecc63d50ffe28ad3308d))
* **ExtConfig/Context:** introduce resolveFilename() method ([dc3239a](https://github.com/labor-digital/typo3-better-api/commit/dc3239a7b7dd317ef644ea5bb09ac5fc28e89f8a))
* **ExtConfig/Icon:** Introduce unified icon configuration option ([d8ada28](https://github.com/labor-digital/typo3-better-api/commit/d8ada28b57848723ed7dc97c2a8c06debc5be323))
* **ExtConfig\MainLoader:** use new configuration lib features ([93d0f20](https://github.com/labor-digital/typo3-better-api/commit/93d0f20f7f3355fabbe1cf5f4c83aca6ecaa3b6e))
* **ExtConfig\MainLoader:** use SiteFacet to resolve sites ([f01c010](https://github.com/labor-digital/typo3-better-api/commit/f01c010709716ff4544c2298ce7e3e3058500951))
* **ExtConfig\PidCollector:** allow removal of registered pids ([b60d47b](https://github.com/labor-digital/typo3-better-api/commit/b60d47b8d7008a85fb265d351d160fae3c49fb7c))
* **ExtConfig\Site:** run handler after site pid handler ([ea3eaf2](https://github.com/labor-digital/typo3-better-api/commit/ea3eaf2c27bd6d6fd0d7104610df45cf05645ddf))
* **ExtConfig\SiteBased:** deprecate SiteKeyProviderInterface in favour of SiteIdentifierProviderInterface ([e7caec0](https://github.com/labor-digital/typo3-better-api/commit/e7caec01492dcf06e3d9e4f2678a2339dc64a167))
* **ExtConfig\SiteBased:** implement ExtendedSiteBasedHandlerInterface ([9aa7d7f](https://github.com/labor-digital/typo3-better-api/commit/9aa7d7f77fff1f0fd3b1dc99a668e0a2132043b1))
* **ExtConfig\TypoScript:** introduce expressionLanguage and preParseFunc options ([deb0e96](https://github.com/labor-digital/typo3-better-api/commit/deb0e9680dd5a0c30f89a971c0626389522e87bf))
* **FieldRenderer:** resolve item proc func items before rendering the value ([f5ff7b0](https://github.com/labor-digital/typo3-better-api/commit/f5ff7b003f61b55ebaba6ca65e8e2f8af21c185c))
* **FormEngine\Addon:** resolve TCA field "basePid" on run-time ([769c53e](https://github.com/labor-digital/typo3-better-api/commit/769c53e1c7e4a81deaaef0a5b7df8d58465560bb))
* **Link\LinkBrowser:** resolve "storagePid" pid value when needed ([968a770](https://github.com/labor-digital/typo3-better-api/commit/968a770427bff6bfe12b8c07212ff358dc3fe88a))
* **Pid:** introduce site-based pid handling ([8d635e3](https://github.com/labor-digital/typo3-better-api/commit/8d635e325f9d6983d921592b93557221e42fd52d))
* **Pid:** only update pids from typoScript if changes were detected ([5d5eaf4](https://github.com/labor-digital/typo3-better-api/commit/5d5eaf47d470009e273508189204830c2645cdee))
* **SiteConfigAwareTrait:** add "siteIdentifier" parameter to getSiteConfig() ([8372468](https://github.com/labor-digital/typo3-better-api/commit/8372468ee046acb23e4de5bb0d1571a12c7112c6))
* **Tca\FieldOption:** "limitToPids" now resolves the pids as formDataProvider ([7ce373e](https://github.com/labor-digital/typo3-better-api/commit/7ce373e4ce6bafdc6796b88355fbf2dd4ff38a9b))
* **Tca\FieldOption\BasePid:** don't resolve "pid" references at build-time ([7c86ee5](https://github.com/labor-digital/typo3-better-api/commit/7c86ee5a42d5fa15aa1dee4ee5b611ae66916a82))
* **TypoContext\Site:** ensure compatibility with site-based pids ([66a84af](https://github.com/labor-digital/typo3-better-api/commit/66a84af0bb040288aa34592fa7ac853b98b5f7ed))
* bump required versions for neunerlei/configuration and neunerlei/arrays ([d7aed3f](https://github.com/labor-digital/typo3-better-api/commit/d7aed3f571b02fb9fde4e789f8d694b15e83c16e))
* **Tca\PostProcessor:** introduce "tca.meta" persistation directly in the configState ([5504f60](https://github.com/labor-digital/typo3-better-api/commit/5504f60fb6f371d86d6c04a690023c33a7cd656c))
* **TcaUtil:** add runWithResolvedItemProcFunc to resolve dynamic items in a TCA field ([54d5d68](https://github.com/labor-digital/typo3-better-api/commit/54d5d68ac1a7ec322030141da9808ebbf83f87b3))
* **TypoContext:** add getTypoContext to StaticTypoContextAwareTrait ([80e54a6](https://github.com/labor-digital/typo3-better-api/commit/80e54a67d5c7d020b6181c03a022fc9c3fd4ec3f))
* **TypoContext\PidFacet:** resolve pid in popups like "linkBrowser" reliably ([bfc1a47](https://github.com/labor-digital/typo3-better-api/commit/bfc1a4716c1d98b9529f7ea99a26b33db40a915f))


### Bug Fixes

* **Database\dbgQuery:** ensure all QueryResultInterface types can be debugged ([24d6b88](https://github.com/labor-digital/typo3-better-api/commit/24d6b886abfef9f6bf533343f68a724a2f83c217))
* **ExtConfig:** ensure all ConfigState instances are in sync with DI ([9807982](https://github.com/labor-digital/typo3-better-api/commit/9807982a0eaf332e456dfe4ffb4da103e98e7939))
* **ExtConfig\MainLoader:** don't unset typo.globals when loading the config state ([d819a04](https://github.com/labor-digital/typo3-better-api/commit/d819a048d06a1d351dfce11dd7e37baa0d85e0ad))
* **NamingUtil:** optimize the execution order of resolveTableName ([355e094](https://github.com/labor-digital/typo3-better-api/commit/355e0940d49eb0291597a98e7875da1777762efe))
* **Tca\CustomFieldPreset:** re-introduce sql column generation for custom fields ([d44131a](https://github.com/labor-digital/typo3-better-api/commit/d44131adaa75130fd11aed7b869d17d7b605f51f))
* **TypoContext\SiteFacet:** ensure SiteFacet does not need SiteFinder or SiteMatcher to be instantiated ([3164e79](https://github.com/labor-digital/typo3-better-api/commit/3164e7994611ac46f53322b14fe3adf45f57c8bb))
* **TypoContext\SiteFacet:** improve site resolution for early requests ([2848111](https://github.com/labor-digital/typo3-better-api/commit/2848111a1146a84a14b06c2af710afb9175d16b4))
* **TypoContext\SiteFacet:** improve site resolution for early requests further ([97795fa](https://github.com/labor-digital/typo3-better-api/commit/97795fad802bb77c33467aaaef97e326737dcb7a))
* **TypoContext\SiteFacet:** make resolved $currentSite aware of the current pid and recompile if needed ([26f1a43](https://github.com/labor-digital/typo3-better-api/commit/26f1a43c34d7524e4692b64549f5d8ca67185892))
* **TypoScript\DynamicTypoScriptRegistry:** use LocallyCachedStatePropertyTrait to retrieve "contents" ([47ba201](https://github.com/labor-digital/typo3-better-api/commit/47ba2019c2c2871f8c4b7eda286e0ebb9a3d1fcf))

### [10.19.1](https://github.com/labor-digital/typo3-better-api/compare/v10.19.0...v10.19.1) (2021-11-16)


### Bug Fixes

* **Tca\TableDumper:** ensure palette showitem definitions are correct ([57041a2](https://github.com/labor-digital/typo3-better-api/commit/57041a2e8d6b75b7117c261370bbb43d89644826))

## [10.19.0](https://github.com/labor-digital/typo3-better-api/compare/v10.18.0...v10.19.0) (2021-11-09)


### Features

* **FalService:** resolve table names through NamingUtil in addFileReference() ([962edd4](https://github.com/labor-digital/typo3-better-api/commit/962edd42aaaf471e72da21c10ecb0a08ef2d206e))


### Bug Fixes

* **Sql:** remove dev fragment ([387062b](https://github.com/labor-digital/typo3-better-api/commit/387062baa7cb3f69bef82aed99ea2ce31e714fc2))
* **Sql:** try to inherit field sql definition if not present on the current type ([0d5b3f0](https://github.com/labor-digital/typo3-better-api/commit/0d5b3f085d945671f06a4a6ee4ea125e58d8c932))
* **TcaBuilder\CustomFieldPreset:** don't modify the SQL definition in custom fields ([7d7b43e](https://github.com/labor-digital/typo3-better-api/commit/7d7b43e75251ac50f47c12e3179e8fb04e27dda0))
* **TcaBuilder\Relations:** generate sys_category opposite field config correctly ([4f83f7a](https://github.com/labor-digital/typo3-better-api/commit/4f83f7a19e334062d186a6bc408cac0c639844cd))
* **TcaBuilder\TcaTable:** remove sql type when removing a TCA type ([dade079](https://github.com/labor-digital/typo3-better-api/commit/dade079fbb121145fe19da20426ac18bd5deec94))

## [10.18.0](https://github.com/labor-digital/typo3-better-api/compare/v10.17.0...v10.18.0) (2021-11-08)


### Features

* **FieldPreset\Inline:** implement "allOpen", "openMultiple" and "noSorting" options ([e30b04e](https://github.com/labor-digital/typo3-better-api/commit/e30b04e8e6a024e31c8a1ff377dae8bf4fd136c8))
* **FormEngine\Addon:** add fieldChangeFunc to inline elements ([6335beb](https://github.com/labor-digital/typo3-better-api/commit/6335beb2912cc1a922a110bb2885825afdb4b02e))
* **Tca\Builder:** implement support for listLabelRenderers on a table level ([285fda6](https://github.com/labor-digital/typo3-better-api/commit/285fda666747dbee7e04c3051b4853628657352a))
* **Tca\FieldOption:** add InlineAppearanceOption ([cba1f0a](https://github.com/labor-digital/typo3-better-api/commit/cba1f0aac4256502dc9f88981410c6ae0ca3f3aa))
* **TcaBuilder:** introduce CustomDisplayConditionInterface for user conditions ([ed871db](https://github.com/labor-digital/typo3-better-api/commit/ed871db8752f6c0642d8ea80202e393d81d46125))
* **TcaBuilder\FieldPreset:** deprecate "default" option on presets ([7b4e800](https://github.com/labor-digital/typo3-better-api/commit/7b4e800cce33b951195371161d35ec82f62bce5b))


### Bug Fixes

* **EventHandler\FormEngineAddon:** update form engine addon event handler ([f3d3da7](https://github.com/labor-digital/typo3-better-api/commit/f3d3da78f9a4654c6f1c1e8d70a7554839c0aff4))
* **Tca\FieldOption:** add limitToPids to configured foreign table ([e3a137c](https://github.com/labor-digital/typo3-better-api/commit/e3a137c57ec6652a55dbadb1ccf43372e33b8191))
* **Tca\FieldOption:** handle array of pids correctly ([03cc9c8](https://github.com/labor-digital/typo3-better-api/commit/03cc9c8d41874e1daf91e16dbcf94a0531c200d5))
* **TcaBuilder\FieldPreset:** ensure empty default option does not override existing defaults ([20ebb56](https://github.com/labor-digital/typo3-better-api/commit/20ebb56e38fa67b8514fc4a08fd38d0c8d2aa50a))
* **TcaBuilder\Input:** remove renderType when setting "input" preset ([17d6be3](https://github.com/labor-digital/typo3-better-api/commit/17d6be352793977d6d2eda94451102f9654993cb))

## [10.17.0](https://github.com/labor-digital/typo3-better-api/compare/v10.16.0...v10.17.0) (2021-11-08)


### Features

* **FormEngine\CustomField:** store initial "type" and "renderType" in config when setting a custom field ([e61ceee](https://github.com/labor-digital/typo3-better-api/commit/e61ceee812e96c9e6c83f2a2d3b7438dffb82ada))
* **Tca\AbstractTypeList:** add "removeType" and "setDefaultTypeName" methods ([8492655](https://github.com/labor-digital/typo3-better-api/commit/8492655b65057268a0087bc311a41f288394bf99))


### Bug Fixes

* **BackendPreviewRenderer:** resolve content element view config correctly ([959c04e](https://github.com/labor-digital/typo3-better-api/commit/959c04efa85e8aeedc6505366c5558e19d90990c))
* **ContentControllerBackendPreviewTrait:** save a unnecessary variable ([cebed2f](https://github.com/labor-digital/typo3-better-api/commit/cebed2f8e327c1ec36840e54465d58c64a91bcdc))
* **Database\DbService:** retrieve PersistenceManager through object manager ([b73dfbc](https://github.com/labor-digital/typo3-better-api/commit/b73dfbc997038d240a339cbe8b86a225378139e9))
* **Database\RelationResolver:** throw an exception if a field config could not be resolved ([50d0961](https://github.com/labor-digital/typo3-better-api/commit/50d096197050fc60715277d4e3418ee153116b0d))
* **ExtConfig\Table:** forcefully reload extBase persistence mapping when required before TCA build ([bd0a4b7](https://github.com/labor-digital/typo3-better-api/commit/bd0a4b7bcbdabf559c5d408f8c00c1c065756b8d))
* **FalService:** create fallback hash for missing files ([fa98f7e](https://github.com/labor-digital/typo3-better-api/commit/fa98f7e5bba92ba73b9ffcfd9b10d3a35ef2a429))
* **FormEngine\CustomField:** add getters for "iconFactory" and "nodeFactory" to context ([addc69a](https://github.com/labor-digital/typo3-better-api/commit/addc69adafdcc58e242fc24eae8e82377c3ad3ed))
* **FormEngine\GroupElementsTriggerReload:** make addon compatible with changed requirements in v10 ([0845743](https://github.com/labor-digital/typo3-better-api/commit/08457434399faa534e192fad140991082dc8bf14))
* **Tca\Builder:** build simple display conditions correctly ([4f553fa](https://github.com/labor-digital/typo3-better-api/commit/4f553fae40498afc92bbdbf6037e9e4fb82f1add))
* **Tca\EvalOption:** ensure eval rules are applied correctly ([af8000a](https://github.com/labor-digital/typo3-better-api/commit/af8000a05f25105cb56ef2990b0120a751d2db09))
* **Tca\Palette:** setHidden() now correctly returns $this ([c8979df](https://github.com/labor-digital/typo3-better-api/commit/c8979df4da0e1871253073ab7bdb31348f43e397))
* **Tca\RelationFile:** handle disabled fal fields correctly ([ad1c5d4](https://github.com/labor-digital/typo3-better-api/commit/ad1c5d40c28bea1f2b1f5b90bfabf8ffb7bae3ae))
* **Tca\TcaTableType:** don't inherit field if no default type exists ([d57b672](https://github.com/labor-digital/typo3-better-api/commit/d57b672b35f81211cf70474de8422c4ef6c39478))
* **Tca\TcaUtil:** ensure runWithResolvedTypeTca works correctly even without columnOverrides ([c3881e9](https://github.com/labor-digital/typo3-better-api/commit/c3881e91409f24d233c0b5fd1d960d4c775b5fe4))
* **Tca\TcaUtil:** ensure that runWithResolvedTypeTca knows about column name changes ([ee453bc](https://github.com/labor-digital/typo3-better-api/commit/ee453bce7c51ae43c2af13e88d2b6745967e2a54))
* **TcaUtil:** getRowValue can now return array values ([65ea603](https://github.com/labor-digital/typo3-better-api/commit/65ea6037fe9ae90be4f1726f6553ce2269d4eeae))

## [10.16.0](https://github.com/labor-digital/typo3-better-api/compare/v10.15.3...v10.16.0) (2021-11-04)


### Features

* **NamingUtil:** add isCallable() helper to validate if a TYPO3 callable definition seems to be valid ([f37c873](https://github.com/labor-digital/typo3-better-api/commit/f37c87377c38e67b6b52116e4e265e742f02bcd3))


### Bug Fixes

* **FormEngine\CustomWizardNode:** extract flex form values correctly ([87e8512](https://github.com/labor-digital/typo3-better-api/commit/87e85124c3223b5cf22aa4d61ac23cedeb4fc790))
* **Tca\InlineForeignFieldOption:** fix typo in foreign field type config ([183d2a2](https://github.com/labor-digital/typo3-better-api/commit/183d2a24c2d2038efa9c8260e1af071d6adcb007))
* **Tca\Relations:** ensure that file relations inside of flex form sections are rendered again ([5f781d6](https://github.com/labor-digital/typo3-better-api/commit/5f781d6c964c9f89fc0ff03513dd65cf6dd4586e))
* **Tca\UserFuncOption:** ensure callable check does not fail for non-static callbacks ([f4f8031](https://github.com/labor-digital/typo3-better-api/commit/f4f8031e1091aef9cb8fc5f7034d3c0bb76b4a67))

### [10.15.3](https://github.com/labor-digital/typo3-better-api/compare/v10.15.2...v10.15.3) (2021-10-29)


### Bug Fixes

* **FormEngine\InlineColPosHook:** ensure that t3ba_inline field is only required when it exists ([cdc08d8](https://github.com/labor-digital/typo3-better-api/commit/cdc08d80afae6df249291c4bfb1fef1fcda86fea))

### [10.15.2](https://github.com/labor-digital/typo3-better-api/compare/v10.15.1...v10.15.2) (2021-10-29)


### Bug Fixes

* **FieldPreset\Slug:** ensure that all options are applied to the slug preset ([4ca2e5c](https://github.com/labor-digital/typo3-better-api/commit/4ca2e5c90c640f7b0d88b6910dd66de71576c7d1))

### [10.15.1](https://github.com/labor-digital/typo3-better-api/compare/v10.15.0...v10.15.1) (2021-10-27)


### Bug Fixes

* **FieldOption\EvalOption:** ensure that allowed types are actually allowed ([c039c73](https://github.com/labor-digital/typo3-better-api/commit/c039c738ebd68a8d351cffba2c17c71193c8a2e4))
* **FormEngine\Inline:** ensure inline contents don't show up in the list view ([1dd144d](https://github.com/labor-digital/typo3-better-api/commit/1dd144d0e7d37e005975af027e011e4d1ac6509f))
* **Tca\Field:** return $this in inheritFrom() method ([919ba48](https://github.com/labor-digital/typo3-better-api/commit/919ba48e29dbf361d9e2a2f5c9435773702d1fbc))

## [10.15.0](https://github.com/labor-digital/typo3-better-api/compare/v10.14.0...v10.15.0) (2021-10-27)


### Features

* **Tca\Builder:** add getField method to containers and tabs ([021328b](https://github.com/labor-digital/typo3-better-api/commit/021328b894910a8e879e9106bb8cc5bb684437c4))
* **Tca\Builder:** build display conditions more reliably ([10e6773](https://github.com/labor-digital/typo3-better-api/commit/10e677327a09384d2d5261690b8431f807a000e6))
* **Tca\Builder:** implement special TCA case handler ([58eb6d7](https://github.com/labor-digital/typo3-better-api/commit/58eb6d726f2edd819c7f66d282bf31b80ef26a1e))
* **Tca\Builder:** moveTo() positions can now be given as array ([524b3b7](https://github.com/labor-digital/typo3-better-api/commit/524b3b71e028f973fa54b334fcf3bc5ce1fc4280))
* **Tca\Dumper:** ensure TCA palette diffs are calculated correctly ([e682c18](https://github.com/labor-digital/typo3-better-api/commit/e682c18b858e0a0f928d53b6663a4240d3b3eb10))
* **Tca\Dumper:** ensure TCA palette diffs are calculated correctly ([780064a](https://github.com/labor-digital/typo3-better-api/commit/780064a6e4c380a6fa92bdb0934571771203c9bf))
* **Tca\FieldPreset:** major rework of the field preset application ([1bf5639](https://github.com/labor-digital/typo3-better-api/commit/1bf5639a5726e9f1c99c4e87fe9a4231bcb8fe2a))
* **Tca\Table:** add modifyPaletteGlobally() method to tca tables ([6b58c3e](https://github.com/labor-digital/typo3-better-api/commit/6b58c3e5af7ad6c962b8630da39fc2bd1e6cd9b5))
* **Tca\TcaBuilderContext:** extract getRealTableNameList() from AbstractFieldPreset ([a9f9a49](https://github.com/labor-digital/typo3-better-api/commit/a9f9a498bcfb1ee1cc3ade3fda78d37cb90c4ec3))


### Bug Fixes

* **FormEngine\Addon\FalFileBaseDir:** harden resolving of group and inline fields ([5cf8003](https://github.com/labor-digital/typo3-better-api/commit/5cf80030266b35beeae6821c89890d697932ba39))
* **Tca\AbstractElement:** add "Flex" as possible return type of getForm() ([899692d](https://github.com/labor-digital/typo3-better-api/commit/899692d6f25d5bc3bc612deb22d6f7af45db7ac1))
* **Tca\Builder:** dump and load palettes correctly ([08a75dc](https://github.com/labor-digital/typo3-better-api/commit/08a75dce2ead39a19d78409e9086a407b4e63ffb))
* **Tca\Builder:** remove dev fragment ([81f4113](https://github.com/labor-digital/typo3-better-api/commit/81f41137d9aef1f56e4ed40d9dc5a27441b25f49))
* **Tca\Dumper:** ensure type palette clones inherit ALL config and not only the showitem value ([1c77f47](https://github.com/labor-digital/typo3-better-api/commit/1c77f4719ab59591a8e54b01d4e1c169b67237a9))
* **Tca\Dumper:** fix typo in comments ([479b826](https://github.com/labor-digital/typo3-better-api/commit/479b826fca8b6ada69f6ce9893c8035e44760c96))

## [10.14.0](https://github.com/labor-digital/typo3-better-api/compare/v10.13.2...v10.14.0) (2021-10-11)


### Features

* **ExtConfig\Table:** implement V11 handling for nested class table names ([c68ebab](https://github.com/labor-digital/typo3-better-api/commit/c68ebab587576f96464390915b633060a20af8c0))


### Bug Fixes

* **ExtConfig\Core:** enable feature toggles immediately after handler execution ([4129027](https://github.com/labor-digital/typo3-better-api/commit/4129027e008d35b324ef0a4122a5516d9f644dd1))
* **Tca\Builder:** ensure "custom fields" allow "NULL" as default value ([f94f4af](https://github.com/labor-digital/typo3-better-api/commit/f94f4af63746053e91a196cee1d790fd1e5d736e))
* **Tca\Builder:** harden the detection of multiple TCA loads in the same request ([84c478a](https://github.com/labor-digital/typo3-better-api/commit/84c478a0b5ca487155b8d44dfa0f0911a08d4eef))

### [10.13.2](https://github.com/labor-digital/typo3-better-api/compare/v10.13.1...v10.13.2) (2021-10-07)


### Bug Fixes

* **Sql:** ensure that type columns don't create "renamedColumns" when merging them ([26f1078](https://github.com/labor-digital/typo3-better-api/commit/26f10789f6ab8d1fbecae37d125e5a102ab9848a))

### [10.13.1](https://github.com/labor-digital/typo3-better-api/compare/v10.13.0...v10.13.1) (2021-10-05)


### Bug Fixes

* **Tca\Builder:** don't dump field label in showitem string if the same is set as "label" ([cb5f107](https://github.com/labor-digital/typo3-better-api/commit/cb5f107e6d6c01410a222c99fa7d2aa7f1609e1e))
* **Tca\Builder:** ensure an exception is thrown when nodes are nested invalidly ([c097332](https://github.com/labor-digital/typo3-better-api/commit/c09733266570d2b76c2233123bffb8e9a99baf58))

## [10.13.0](https://github.com/labor-digital/typo3-better-api/compare/v10.12.0...v10.13.0) (2021-10-04)


### Features

* **Tca\Builder:** allow removing existing displayConditions through setDisplayCondition ([6523219](https://github.com/labor-digital/typo3-better-api/commit/6523219b92b99370a0a4d47381209e333bf81537))
* **Tca\Builder:** unify field "size" calculation ([338d490](https://github.com/labor-digital/typo3-better-api/commit/338d490d6346a5661a6f194ecf122028e4a5b2f9))


### Bug Fixes

* **FieldPresetApplier:** use makeInstance() to resolve applier instead of getService() ([e524707](https://github.com/labor-digital/typo3-better-api/commit/e52470710c6ba3dd9985f416009c4d89cf016ea6))
* **Sql:** ensure that text columns are created allowing "NULL" ([f707d86](https://github.com/labor-digital/typo3-better-api/commit/f707d865f38ddfa81bc70522d42871a9e529f670))
* **Tca\Builder:** apply position of line breaks correctly ([21a61ba](https://github.com/labor-digital/typo3-better-api/commit/21a61ba4e122c6da5395b4a359b988c8a351c12d))
* **Tca\Builder:** ensure numeric palette keys are allowed ([06dc157](https://github.com/labor-digital/typo3-better-api/commit/06dc157e092eb231819bd554e48aa6820890cb63))

## [10.12.0](https://github.com/labor-digital/typo3-better-api/compare/v10.11.3...v10.12.0) (2021-09-16)


### Features

* **CustomFormElement:** allow rendering of fluid template strings ([3deaa9d](https://github.com/labor-digital/typo3-better-api/commit/3deaa9d323c77898e8b14f18c33db10e82d3ccab))
* **ExtConfig\ContentType:** allow registration of backend preview and list label renderers through content type classes ([5f4245b](https://github.com/labor-digital/typo3-better-api/commit/5f4245bf889144130245be911f094d865f9e9187))
* **FieldPreset:** add "linkPhone" field preset ([100be79](https://github.com/labor-digital/typo3-better-api/commit/100be79f3ee38516c76d8bae51472baa5c816bbb))
* **FieldPreset:** allow usage of the "applyRelationGroupOpposite" for "relationSelect" presets as well ([24a1376](https://github.com/labor-digital/typo3-better-api/commit/24a1376e3cdb2dc0a31b982d3bd1d04d184fe91f))
* **FileInfo:** add isHidden method to detect if files are hidden or not ([2efeb52](https://github.com/labor-digital/typo3-better-api/commit/2efeb529eacd1604519734eb6d6db99b2dfb3013))
* **TcaBuilder:** allow "TRUE" in "basePid" options to reference the local PID ([c875518](https://github.com/labor-digital/typo3-better-api/commit/c8755182101bfdfa07643e94ee21e5b2f4c6339e))
* **Upgrade:** introduce abstract TtContentUpgradeWizard ([64dc8e0](https://github.com/labor-digital/typo3-better-api/commit/64dc8e079fccca6d108dafe789a6de340681f99c))


### Bug Fixes

* **BackendListLabelRenderer:** try to create renderer instance if not registered as service ([2f60c89](https://github.com/labor-digital/typo3-better-api/commit/2f60c89b933bdd625b81e467fb758ff7ba9cb6e0))
* **FieldRenderer:** handle rendering of file previews correctly ([c2166f0](https://github.com/labor-digital/typo3-better-api/commit/c2166f0f0a742c0ee52786c1c8244543110d0bcc))
* **FormEngine:** resolve base pids with "true" correctly into numeric pid ([e94c548](https://github.com/labor-digital/typo3-better-api/commit/e94c548fe3e8e72f5eedeaa6db0eed00208b32b8))
* **SiteFacet:** add typehint for getAll() method ([5d36ded](https://github.com/labor-digital/typo3-better-api/commit/5d36dedf6463b53a474565e5c704e49747d66841))

### [10.11.3](https://github.com/labor-digital/typo3-better-api/compare/v10.11.2...v10.11.3) (2021-09-08)


### Bug Fixes

* **Rendering\FieldRenderer:** ensure "group" fields are rendered correclty ([a2fe3bf](https://github.com/labor-digital/typo3-better-api/commit/a2fe3bfb1820c69bd552359d90c40f10877800f3))

### [10.11.2](https://github.com/labor-digital/typo3-better-api/compare/v10.11.1...v10.11.2) (2021-09-08)


### Bug Fixes

* **Database:** ensure related rows are resolved correctly ([24e97d9](https://github.com/labor-digital/typo3-better-api/commit/24e97d90cc2241c6013dc70ebd7a2993514663fd))

### [10.11.1](https://github.com/labor-digital/typo3-better-api/compare/v10.11.0...v10.11.1) (2021-09-01)


### Bug Fixes

* **RelatedRecordRow:** use Hydrator instead of DataMapper to create related row models ([78e249c](https://github.com/labor-digital/typo3-better-api/commit/78e249c2f150a32b26dbb4753a795359629687a1))
* **StandaloneBetterQuery:** resolve model table map correctly in getRelated ([14094cf](https://github.com/labor-digital/typo3-better-api/commit/14094cf46c4ffe94a86209b4847bf28f59d499b2))

## [10.11.0](https://github.com/labor-digital/typo3-better-api/compare/v10.10.0...v10.11.0) (2021-08-20)


### Features

* **ExtConfig\Frontend:** add favicon registration option ([0560a5d](https://github.com/labor-digital/typo3-better-api/commit/0560a5dc9ecc4f1b163cf891e44ea1aee6b17659))


### Bug Fixes

* **BetterQuery:** ensure good exception message in getRelated() method ([2b18d98](https://github.com/labor-digital/typo3-better-api/commit/2b18d987d2b4f5d1267097ee12e9bbd5f055aa76))
* **Cache:** don't break other injection methods when using CacheConsumer ([d8fda76](https://github.com/labor-digital/typo3-better-api/commit/d8fda76329bf70dcf2f3bb46d05241f1473cd681))
* **FalService:** transform table name references into actual table names ([3df1807](https://github.com/labor-digital/typo3-better-api/commit/3df1807dfba5908c3d362a5a8abc6103398c9e85))

## [10.10.0](https://github.com/labor-digital/typo3-better-api/compare/v10.9.0...v10.10.0) (2021-08-19)


### Features

* **Cache:** implement runtime cache implementation ([59ad891](https://github.com/labor-digital/typo3-better-api/commit/59ad891be5f87aac071ab23335e4d302c0aad7ee))


### Bug Fixes

* **CacheConfigurationPass:** make sure autowiring configuration is generated correctly ([76a3313](https://github.com/labor-digital/typo3-better-api/commit/76a33133eb23afdbf2ffa3b68eeff87106114a69))
* **ExtConfig\Extbase:** ensure iconArgs don't include NULL values when cached ([7925bc3](https://github.com/labor-digital/typo3-better-api/commit/7925bc3dd1da71e900eae97e74cd8245798889b5))

## [10.9.0](https://github.com/labor-digital/typo3-better-api/compare/v10.8.1...v10.9.0) (2021-08-17)


### Features

* **Tca:** implement getRootLevel and setRootLevel helpers to a tca table ([e6327f6](https://github.com/labor-digital/typo3-better-api/commit/e6327f681e2061c0e66bdfc19a50fde47d7ee56f))
* **Tca\Presets:** improve applyLink() options ([9efcf39](https://github.com/labor-digital/typo3-better-api/commit/9efcf3939c0a69e9ab8f9b3948ed8cb074689754))
* **Tca\TableFactory:** only add the "iconfile" option if an icon could be resolved ([125e0ca](https://github.com/labor-digital/typo3-better-api/commit/125e0cab1ee8b38492372cb8621b107bcfc4cb2f))

### [10.8.1](https://github.com/labor-digital/typo3-better-api/compare/v10.8.0...v10.8.1) (2021-07-27)


### Bug Fixes

* **TemplateRenderingService:** use backend translator in mustache templates when possible ([46fd8e5](https://github.com/labor-digital/typo3-better-api/commit/46fd8e52647c1dddb03d1279015ed9aafe20baf4))

## [10.8.0](https://github.com/labor-digital/typo3-better-api/compare/v10.7.1...v10.8.0) (2021-07-27)


### Features

* **ExtConfig\Routing:** implement registration of backend routes through ext config ([3563105](https://github.com/labor-digital/typo3-better-api/commit/35631053e06ec8d76683953cdb8cfee1c3e59e95))
* **FormEngine\CustomField:** improve core integration ([5de3598](https://github.com/labor-digital/typo3-better-api/commit/5de3598347ca1d774008046768e3f92d42aafd9f))
* **PageService:** add "force" option to additional methods ([9536f5a](https://github.com/labor-digital/typo3-better-api/commit/9536f5a9f937b50f14e1733d86a0b12538eb5cd0))
* **PageService:** improve renderPageContents and getPageContents with new options ([784e9dc](https://github.com/labor-digital/typo3-better-api/commit/784e9dca079a25e5b5e82594643945d390314fbe))
* **Rendering:** implement renderFluid() in TemplateRenderingService to render standalone fluid template string ([4d2db5c](https://github.com/labor-digital/typo3-better-api/commit/4d2db5c2beae8a4c067655c58004d6a0def8523e))
* **TcaUtil:** add getLanguageUid() to extract language uid from a record ([67c9d3f](https://github.com/labor-digital/typo3-better-api/commit/67c9d3fb27861214a44067e945f62f522db26467))
* **Tool\DataHook:** add registration methods of the most common data hooks to DataHookCollectorTrait ([e40a854](https://github.com/labor-digital/typo3-better-api/commit/e40a85428183ae5927d5afab8f31a9466021902a))


### Bug Fixes

* **BetterQuery:** fix broken "in" handling in DoctrineQueryAdapter ([bb25ad8](https://github.com/labor-digital/typo3-better-api/commit/bb25ad8f63f2ff27eaaf96e82bd916870b078949))
* **BetterQuery:** make sure getFirst returns null instead of false when no result was found ([2510bf0](https://github.com/labor-digital/typo3-better-api/commit/2510bf06c00e9ed00757a1ff1fa34e97fdaa5b4d))
* **BetterQuery:** special "uid" lookup is now disabled if the language should not be respected ([c159131](https://github.com/labor-digital/typo3-better-api/commit/c1591313524629e02d3154fd10234ebebd916815))
* **CustomFieldContext:** make sure getRecordUid() returns the record uid instead of the page uid ([00050d8](https://github.com/labor-digital/typo3-better-api/commit/00050d890f614c67bc1b8e43d9fcb6cff6458ccd))
* **DataHook:** add missing data hook types to the type validator ([e56da78](https://github.com/labor-digital/typo3-better-api/commit/e56da785f4de47a69335634c7296ca96022343b8))
* **DataHook:** make sure action processor provides changes back to data handler ([b679d04](https://github.com/labor-digital/typo3-better-api/commit/b679d04e8ba0f88ba59b69f51717460781609dc6))
* **DataHook:** make sure copy events are triggered once per copied entry ([1d901ef](https://github.com/labor-digital/typo3-better-api/commit/1d901ef7ce6a7dab9c8a917c3319f274d2c7122c))
* **DataHook:** make sure onActionPostProcessor always has a row to process ([b3b1c08](https://github.com/labor-digital/typo3-better-api/commit/b3b1c08caf39da3503a62e332d3a64086afe0f27))
* **FormEngine:** let CustomElementInterface extend PublicServiceInterface ([ce58dc3](https://github.com/labor-digital/typo3-better-api/commit/ce58dc3b3708d83138625d6c9fadf28a0d095753))
* **NamingUtil:** gather list of plugin names correctly in pluginNameFromControllerAction() ([3450fbe](https://github.com/labor-digital/typo3-better-api/commit/3450fbe56e72fa2f97c8c3484677003226836e25))
* **PageService:** use getPageDataHandler() in createNewPage ([951ba01](https://github.com/labor-digital/typo3-better-api/commit/951ba0103994900ee6f5132b7aff8468b8f6d993))
* **RouteEnhancerSchema:** make error message on failing plugin definition more verbose ([dc75895](https://github.com/labor-digital/typo3-better-api/commit/dc75895e6ea4a381d3fa417f2b9bd6233ba108ad))
* **VisibilitySimulationPass:** child simulations now inherit the outer visibility correctly ([d46728e](https://github.com/labor-digital/typo3-better-api/commit/d46728e88b5cd90b9c9eddab2f4976440430f0bf))

### [10.7.1](https://github.com/labor-digital/typo3-better-api/compare/v10.7.0...v10.7.1) (2021-07-22)


### Bug Fixes

* **ExtendedSiteConfiguration:** make sure to clear site cache when the record gets saved ([47cd62c](https://github.com/labor-digital/typo3-better-api/commit/47cd62c5a4c79b968e28aaf2da633bdad5a76d0e))

## [10.7.0](https://github.com/labor-digital/typo3-better-api/compare/v10.6.0...v10.7.0) (2021-07-22)


### Features

* **ExtConfig:** implement support to register upgrade wizards using ext config ([4592e44](https://github.com/labor-digital/typo3-better-api/commit/4592e44c76254cc7311314371e272395f70352c6))
* **FieldPreset:** create only one MM table per parent table ([2fa8429](https://github.com/labor-digital/typo3-better-api/commit/2fa8429b8d0115d0495543ab277b745984fd63b6))
* **Tca\ContentType:** implement v11 implementation of content type naming schemas ([f9d21ab](https://github.com/labor-digital/typo3-better-api/commit/f9d21ab7901999b468dce3a0eeb92d55a0dc416a))
* **Tca\FieldPreset:** implement mm opposite usage better into the relations preset ([1bd163f](https://github.com/labor-digital/typo3-better-api/commit/1bd163f2d7f43b172908d4a1730b8322cfd2abe8))
* **ViewHelpers:** add add inlineContent view helper for rendering related content elements ([609de7f](https://github.com/labor-digital/typo3-better-api/commit/609de7f73d9c8b21b0845301ed92af8807260520))
* **ViewHelpers:** implement page title viewhelper to set the page title out of fluid templates ([2cf8918](https://github.com/labor-digital/typo3-better-api/commit/2cf891892877911b4acb1e191b2c6f3fdb21a980))
* implement basic facade to interact with feature toggles ([6af8271](https://github.com/labor-digital/typo3-better-api/commit/6af82717fa45f73cf13c558527d98cb2a6241e3f))


### Bug Fixes

* **Database\BetterQuery:** disable default language handling in better query when no site exists ([76f77d4](https://github.com/labor-digital/typo3-better-api/commit/76f77d486e8eb309058108d066b116d6ef9cb0bf))
* **Database\dbgQuery:** fix broken output of raw queries ([e619896](https://github.com/labor-digital/typo3-better-api/commit/e619896e3fbff899705efad38cf96a5d13f06fa9))
* **FieldPreset\Inline:** create a foreign match field for the child table ([3cf42b9](https://github.com/labor-digital/typo3-better-api/commit/3cf42b97f73bafe6a390b6717a3e3e99e8ba0c16))
* **FieldPreset\Inline:** create a foreign match field for the child table if needed ([ba187b0](https://github.com/labor-digital/typo3-better-api/commit/ba187b05e3a34cb478a356103a95e494a0ed513a))
* **FieldPreset\Inline:** make sure tt_content colPos column is signed ([dc4662b](https://github.com/labor-digital/typo3-better-api/commit/dc4662bc5cc548cc6f1ef31d3e8879407369dcef))
* **FieldPreset\Inline:** revert commit 3cf42b97, because it will break existing installations ([0c774c7](https://github.com/labor-digital/typo3-better-api/commit/0c774c7141e9295c438ec6db27294027adb6ba92))
* **FormEngine\Custom:** make inclusion of js and css in form fields and wizards more reliable ([413aea4](https://github.com/labor-digital/typo3-better-api/commit/413aea49407be63eb510c5b5f0a8264cbf1aa466))
* **Tca\FieldPreset:** move new traits into correct namespace ([2704336](https://github.com/labor-digital/typo3-better-api/commit/27043363e3795068840a86a1aad2bc5fd56deb84))

## [10.6.0](https://github.com/labor-digital/typo3-better-api/compare/v10.5.0...v10.6.0) (2021-07-16)


### Features

* **FormEngine:** implement new content element wizard, for inlineContent field preset ([50c2881](https://github.com/labor-digital/typo3-better-api/commit/50c288127dcf4e0581ebd91f773523529e0f706c))
* **FormEngine\Inline:** improve inlineContent preset ([6f8ed71](https://github.com/labor-digital/typo3-better-api/commit/6f8ed7198a9ff1878e3aadec31f0e2bcd25d0ac6))
* **Rendering:** implement rendering of inline content element previews ([70bee70](https://github.com/labor-digital/typo3-better-api/commit/70bee70d02e4bd787da27c116fb243a584ee8964))


### Bug Fixes

* **BackendPreview:** make list label rendering more reliable ([56c573d](https://github.com/labor-digital/typo3-better-api/commit/56c573d0b7c93a6f04df0123cf88674ae91dd246))
* **BackendPreview:** prevent "inline" elements being displayed as "unused" ([00ed140](https://github.com/labor-digital/typo3-better-api/commit/00ed1408cfcd1fab25fbaf1f8bb7bc8cf7a1cc0d))
* **BackendPreviewRenderer:** render descriptions of elements not in the new ce wizard, as well ([b97a54a](https://github.com/labor-digital/typo3-better-api/commit/b97a54ab4d380a2ef796419baf94c52f4d77e374))
* **BetterQuery\Standalone:** ensure handleTranslationAndVersionOverlay always returns an array ([e068ad4](https://github.com/labor-digital/typo3-better-api/commit/e068ad43c2e7d91bdc8e6fc63b6f13d135d20422))
* **Database\BetterQuery:** fix an issue when uid where constraints are negated ([2e803e7](https://github.com/labor-digital/typo3-better-api/commit/2e803e7cf6c3868aacb12103ba39c6334bb71c4e))
* **ExtConfig\TypoScript:** fix error when typo.typoScript.staticDirectories was empty ([e45b0e7](https://github.com/labor-digital/typo3-better-api/commit/e45b0e7412b764ec2a2fc49bf5a5d3ce802a315b))
* **Fal:** allow usage of addFileReference() on hidden records ([eaf072d](https://github.com/labor-digital/typo3-better-api/commit/eaf072dc1e3eb33b434f9c56741ba03be3fe2f8e))
* **FieldPreset\Inline:** set default values for created foreign fields ([ff2316b](https://github.com/labor-digital/typo3-better-api/commit/ff2316bf59df7a8b679d210215434de7099262d1))
* **FormEngine\Inline:** always move content elements to colPos -88 ([d928710](https://github.com/labor-digital/typo3-better-api/commit/d92871063e9cfe0c7139d966656feac9fc9ae2d7))
* **Rendering\FieldList:** fix table style definition ([98984f5](https://github.com/labor-digital/typo3-better-api/commit/98984f5ff655bf067ddc462a8e08625997b20584))
* **Rendering\FieldRenderer:** fix rendering for file and folder links ([d11efed](https://github.com/labor-digital/typo3-better-api/commit/d11efedcb691bdb676a785e10c854d9a3df70f60))
* **Tca\ContentType:** resolve display conditions with short field names correctly ([5cd0b5e](https://github.com/labor-digital/typo3-better-api/commit/5cd0b5e2a7c1ff297468a62b7ae4e28096a2e0ba))
* **Tca\FlexForm:** resolve flex form definitions on tt_content more reliably ([0c7e652](https://github.com/labor-digital/typo3-better-api/commit/0c7e65237afee2d33ddbca7456652ca0d02cfd6a))
* **Tool\DataHandler:** make exception message more speaking ([6f11142](https://github.com/labor-digital/typo3-better-api/commit/6f111429c89d9b480048f5d42904aff702d7aef7))

## [10.5.0](https://github.com/labor-digital/typo3-better-api/compare/v10.4.0...v10.5.0) (2021-07-15)


### Features

* **Tca:** implement support to generate a record preview url in the backend ([2fe4a92](https://github.com/labor-digital/typo3-better-api/commit/2fe4a92babc46ccba244ec9f86aef45f5fbfeb18))


### Bug Fixes

* **Cache\FrontendCache:** make isUpdate detection more reliable ([4eaed13](https://github.com/labor-digital/typo3-better-api/commit/4eaed132a20ab7dfa92e44b7ea00774720ed17b0))
* **ClassOverrideGenerator:** remove unused code segment ([ec91f59](https://github.com/labor-digital/typo3-better-api/commit/ec91f59675fffd8e0b3300ce0672692865c09be3))
* **Tca\PostProcessor:** use generic tca.meta.tsConfig config instead of tca.meta.backend.listPosition ([af1600f](https://github.com/labor-digital/typo3-better-api/commit/af1600f25ad9068b85162086a7801cc8d702786a))
* **TypoScript:** don't retrieve TypoScriptParser through the service container ([72945a6](https://github.com/labor-digital/typo3-better-api/commit/72945a6658ecf8e80da2d31eb94d3e350e1de937))

## [10.4.0](https://github.com/labor-digital/typo3-better-api/compare/v10.3.0...v10.4.0) (2021-07-06)


### Features

* **Tca:** implement ConfigureContentTypeInterface config option ([e39df2d](https://github.com/labor-digital/typo3-better-api/commit/e39df2dc017d8ad10356d47ad2ec7166d9e28770))
* **Tca\Table:** add raw post processor registration to table class ([de94233](https://github.com/labor-digital/typo3-better-api/commit/de94233e43c860be9b752e84cc901bf228d29ee6))


### Bug Fixes

* **dbgQuery:** dump less clutter when working in the CLI ([a986ab9](https://github.com/labor-digital/typo3-better-api/commit/a986ab99173a923413db6f89fa140c125ddc52b1))
* **ExtConfig\ExtBase:** remove unused code fragment ([ffda7b4](https://github.com/labor-digital/typo3-better-api/commit/ffda7b444c7ac2842dd81aff7987ebdbd23c2229))
* **SqlRegistry:** fix an issue that occurs in CLI environments with APCu disabled ([e1467db](https://github.com/labor-digital/typo3-better-api/commit/e1467db88686f5a64666536944453c100aef600a))
* **Tca\Builder:** fix issues when showitem of palettes is empty ([0a74280](https://github.com/labor-digital/typo3-better-api/commit/0a742806bfe61e08e81e7d1277a1034a452edf82))
* **Tca\ContentType:** don't apply ct_child field when no table maps exist ([f3d4576](https://github.com/labor-digital/typo3-better-api/commit/f3d45761df71239ccf915fb89b1a650cf694fa38))
* **Tca\ContentType:** fix multiple issues when handling content type rows ([03d5909](https://github.com/labor-digital/typo3-better-api/commit/03d5909b7f6cc6c59277f312872ef0addb8d8968))
* **Tca\FieldPreset:** add default value for SQL string types in input fields ([d7c926c](https://github.com/labor-digital/typo3-better-api/commit/d7c926c3b1cd99891f4a5539b9bc9decba2c03fa))
* remove dev-only fragment ([c025575](https://github.com/labor-digital/typo3-better-api/commit/c02557551ee146ecac3665c01327250b4c8296d4))

## [10.3.0](https://github.com/labor-digital/typo3-better-api/compare/v10.2.0...v10.3.0) (2021-07-02)


### Features

* **ExtConfig\Frontend:** add configuration options for meta tags ([8ce8476](https://github.com/labor-digital/typo3-better-api/commit/8ce847628e96daba0721868494b9b71e55ad21ec))
* **ExtConfig\Frontend:** add configuration options for raw footer and header html ([4f9dd91](https://github.com/labor-digital/typo3-better-api/commit/4f9dd91fd6952f9d1559928a8202fcd2bf1ecf9b))
* **ViewHelpers:** implement tsValue view helper to extract ts from a path ([482307e](https://github.com/labor-digital/typo3-better-api/commit/482307e6d4ec4648e0161d1eeac6375e80be6734))


### Bug Fixes

* **BackendPreviewRenderer:** avoid issues when previews of a hidden page are rendered ([2d70ca4](https://github.com/labor-digital/typo3-better-api/commit/2d70ca4370f00ece7193e24408020741a22bd491))
* **FieldPreset:** fix translation labels of imageAlignment preset ([0df52e9](https://github.com/labor-digital/typo3-better-api/commit/0df52e9669fafc218af5920cf4beedd7f148e86b))
* **Rendering\FieldRenderer:** catch exceptions if an image should be rendered but was not found ([f2249c8](https://github.com/labor-digital/typo3-better-api/commit/f2249c87df9319ca15cf54f3934afe20cbfe4b59))
* **Tca\Builder:** addConfig() merges numeric array keys strictly now ([5e0d084](https://github.com/labor-digital/typo3-better-api/commit/5e0d084f248193eef6c4d5305917188ea73e7551))
* **Tca\FieldPreset:** remove wrong type declaration for addDefaultOptions() $default ([54e81c2](https://github.com/labor-digital/typo3-better-api/commit/54e81c23c80eae9859e9240b73e50fa39561de20))

## [10.2.0](https://github.com/labor-digital/typo3-better-api/compare/v10.1.2...v10.2.0) (2021-06-28)


### Features

* bump typo3-better-api-composer-plugin to major version 4.0.0 ([e8e6fb0](https://github.com/labor-digital/typo3-better-api/commit/e8e6fb0acbd435ad9ba90dc53e230578d5519d6b))

### [10.1.2](https://github.com/labor-digital/typo3-better-api/compare/v10.1.1...v10.1.2) (2021-06-27)

### [10.1.1](https://github.com/labor-digital/typo3-better-api/compare/v10.1.0...v10.1.1) (2021-06-27)

## [10.1.0](https://github.com/labor-digital/typo3-better-api/compare/v10.0.0-beta.15...v10.1.0) (2021-06-27)

## [10.0.0-beta.15](https://github.com/labor-digital/typo3-better-api/compare/v10.0.0-beta.14...v10.0.0-beta.15) (2021-06-27)

## [10.0.0-beta.14](https://github.com/labor-digital/typo3-better-api/compare/v10.0.0-beta.13...v10.0.0-beta.14) (2021-06-27)


### Features

* **ExtBase:** implement handleNotFound() method to BetterActionController ([82c1816](https://github.com/labor-digital/typo3-better-api/commit/82c1816792fed685445b7e57813bd5962c5a176b))


### Bug Fixes

* minor code style adjustments ([286a526](https://github.com/labor-digital/typo3-better-api/commit/286a5268a5be12a4ae893d8c5aa46602931fc26f))
* **Link:** use lower case config facet ([c8494b6](https://github.com/labor-digital/typo3-better-api/commit/c8494b6983f2512155377cb7889f5f4f484430bc))
* **Tca:** fix incorrect field layout meta sort order ([9b018fd](https://github.com/labor-digital/typo3-better-api/commit/9b018fd6a14b5447a7344982afe256cbd887a97e))

## [10.0.0-beta.13](https://github.com/labor-digital/typo3-better-api/compare/v10.0.0-beta.12...v10.0.0-beta.13) (2021-06-27)


### Features

* **BackendPreview:** move FieldListRenderer to Tool\Rendering namespace ([065c968](https://github.com/labor-digital/typo3-better-api/commit/065c968bd6e19d11a9cfa387ce09f78bb44d92d3))
* **Kernel:** implement onInitHooks ([c6f5de0](https://github.com/labor-digital/typo3-better-api/commit/c6f5de043f3ee153c3ff79e2687d0e41594c5681))
* **Rendering:** improve field and record table renderer output ([2ae8e4a](https://github.com/labor-digital/typo3-better-api/commit/2ae8e4acbd14bff9e3df101f78076f6b52e24a48))


### Bug Fixes

* **ContentTypeApplier:** further harden virtual column removal ([0d39a30](https://github.com/labor-digital/typo3-better-api/commit/0d39a30f48c2bb840bb1773d0ec7226a168d1b0f))
* **ContentTypeApplier:** make sure all virtual columns get dropped if no extension table exists ([4491f27](https://github.com/labor-digital/typo3-better-api/commit/4491f2773c7e44caa4fe5c8cd4845cb58db3e9e1))
* **Event\SaveEventAdapter:** don't emit SaveAfterDbOperationsEvent if an error occurred ([9fff219](https://github.com/labor-digital/typo3-better-api/commit/9fff2191230302046091fd92d049bd5d0ef1cbbe))
* **ExtConfigHandler:** remove unnecessary NotFoundException ([1a98329](https://github.com/labor-digital/typo3-better-api/commit/1a98329ee1760753bfb1f270ed2aa0c9e8a95bdf))
* **FieldPreset:** fix visibility issue on date fields + automatically use "int" as storage type ([30b1548](https://github.com/labor-digital/typo3-better-api/commit/30b1548b808f2ac9b30fab90364f390697e3c84f))
* **NamingUtil:** prefer using getServiceOrInstance() and use PHP8 function names ([949ec27](https://github.com/labor-digital/typo3-better-api/commit/949ec272f011e3168e1941819e9903e74bc5c986))
* **TableDefaults:** use frames palette under tabs.appearance ([0dddf37](https://github.com/labor-digital/typo3-better-api/commit/0dddf37a2ae371acbca937ff72889f9f24114c80))
* **Tca\Field:** generate palette variants more reliably ([cb5ee95](https://github.com/labor-digital/typo3-better-api/commit/cb5ee95ccc25fa06784b80ca708b5dd8fd85bed9))
* **TypoContext:** make getServiceOrInstance public on DependencyInjectionFacet ([6b97f2c](https://github.com/labor-digital/typo3-better-api/commit/6b97f2ca3b62346c09205514f298cc33f58b4f66))

## [10.0.0-beta.12](https://github.com/labor-digital/typo3-better-api/compare/v10.0.0-beta.11...v10.0.0-beta.12) (2021-06-22)


### Features

* **ContainerAwareTrait:** make hasService() more intuitive and introduce getServiceOrInstance() ([aea0d3e](https://github.com/labor-digital/typo3-better-api/commit/aea0d3e8bd0a7dcd54daaa7cd63faae97f792bd6))
* **Link:** implement "pluginTarget" to set "forMe" build option on an object level ([9544c62](https://github.com/labor-digital/typo3-better-api/commit/9544c624059be6f25a7d515e3527ef11bfe7e7a3))
* **Tool\Link:** introduce getLink() to BetterActionController. Better link integration ([f1c4e6d](https://github.com/labor-digital/typo3-better-api/commit/f1c4e6da05437a454adc9dedc524da0ecaa4c3cc))
* **TypoContext:** implement getSiteBasedConfigValue to config facet ([9858d33](https://github.com/labor-digital/typo3-better-api/commit/9858d3396f653b148c57e1ccff7244777ab4812c))


### Bug Fixes

* **BackendPreviewRenderer:** convert response content to string ([270ce11](https://github.com/labor-digital/typo3-better-api/commit/270ce1167a82dd40a76bb3dafdfd8218b563d532))
* **Cache:** make EnvironmentCacheKeyGenerator aware of the current "preview" setting ([abbfd6e](https://github.com/labor-digital/typo3-better-api/commit/abbfd6e0f66481aef8b87f48a46ea728293938be))
* **CacheUtil:** make cache tag generation more reliable for file references and generics ([687314b](https://github.com/labor-digital/typo3-better-api/commit/687314b96f8e99b55c447a68b46ba062eecac1fd))
* **ContentControllerBackendPreviewTrait:** generate the plugin name correctly ([23b8f94](https://github.com/labor-digital/typo3-better-api/commit/23b8f940020e48ce33e39a46632725da02a52c21))
* **ContentControllerDataTrait:** only resolve $row through content object if no preview renderer exists ([9cf8d03](https://github.com/labor-digital/typo3-better-api/commit/9cf8d03b816064f319120b6a819bbd614e8b562e))
* **Simulation\Visibility:** reinitialize page repository when includeHiddenPages flag changes ([927afd9](https://github.com/labor-digital/typo3-better-api/commit/927afd9125d7052655f3979190c437e439120acd))
* **TypoScriptService:** automatically replace INTintScripts in rendered output ([4c70249](https://github.com/labor-digital/typo3-better-api/commit/4c702498ff8ab48e9fb839a649e4f0dc60585938))

## [10.0.0-beta.11](https://github.com/labor-digital/typo3-better-api/compare/v10.0.0-beta.10...v10.0.0-beta.11) (2021-06-16)


### Features

* **BackendPreview\FieldRenderer:** render images horizontally instead of a list ([f3fba74](https://github.com/labor-digital/typo3-better-api/commit/f3fba74908b0de9ebca5b16dbb1c8865363d8a54))
* **Fal:** allow generation of relative image and file urls ([d5f7690](https://github.com/labor-digital/typo3-better-api/commit/d5f769037f24ea4f1d4bd5920ff8b969a92718c7))
* **RoutingConfigurator:** unbind middleware name generation from current ext key and vendor ([484b0de](https://github.com/labor-digital/typo3-better-api/commit/484b0ded761867128cb32650ea9944533efac74f))


### Bug Fixes

* **BackendListLabelRenderer:** remove leading pipe to fix display in record edit screen ([f3b646c](https://github.com/labor-digital/typo3-better-api/commit/f3b646c8f09a335be2f36bb7254302be4bc8d364))
* **ContentControllerDataTrait:** don't fail in getData() if configurationManager is not defined ([300066b](https://github.com/labor-digital/typo3-better-api/commit/300066b86ab057761094124c4f711cd4024fdbf3))
* **ExtConfig\ExtBase:** only register preview hooks if preview renderers have been registered ([d1f2af3](https://github.com/labor-digital/typo3-better-api/commit/d1f2af3814c13a90233274133c08e2ea4b692aac))

## [10.0.0-beta.10](https://github.com/labor-digital/typo3-better-api/compare/v10.0.0-beta.9...v10.0.0-beta.10) (2021-06-16)


### Features

* **AbstractElementConfigurator:** implement missing removeVariant() method ([f4a7b3a](https://github.com/labor-digital/typo3-better-api/commit/f4a7b3af6357684c1c1015f5f647e684ca74cdef))
* **AbstractExtendedCache:** callable "enabled" and "lifetime" options retrieve the current state ([a08b669](https://github.com/labor-digital/typo3-better-api/commit/a08b6697f87da9cfa66029d7a45edaef5427f795))
* **AbstractGroupExtConfigHandler:** make getElementKeyForClass() $postProcessor more reliable ([0fe386d](https://github.com/labor-digital/typo3-better-api/commit/0fe386d79b833363c2e962293f08cca812fb9e47))
* **BackendPreview:** implement legacy support for the hook based setup ([0fb65cf](https://github.com/labor-digital/typo3-better-api/commit/0fb65cf4b5d8ba0e6c3daea89960a096bd3b9b61))
* **BackendPreview:** implement plugin variant based render method resolution ([4f100db](https://github.com/labor-digital/typo3-better-api/commit/4f100dbedbc44627699a6e0d16ed16fa66294078))
* **ContentType:** rename setModelClass() to setDataModelClass() ([bd2105a](https://github.com/labor-digital/typo3-better-api/commit/bd2105ad3fb45449eb18f5c92b361ad0b75b9ece))
* **ContentType:** standardize attribute names + implement variant support ([27c6bf5](https://github.com/labor-digital/typo3-better-api/commit/27c6bf5536e983d88dea48ebbeb1aa573d330ae7))
* **dbgQuery:** support for typo3 query builder instances ([3384f24](https://github.com/labor-digital/typo3-better-api/commit/3384f2492d476d0fdcb7cd24fa4ced0c281a9626))
* **ExtConfig:** implement SiteConfigAwareTrait helper trait ([99bc67d](https://github.com/labor-digital/typo3-better-api/commit/99bc67d5f22900ce94b7cb70c276c9e0ee1c5cd5))
* **PidFacet:** fetch the current id on shortcut pages more reliably ([861d5cb](https://github.com/labor-digital/typo3-better-api/commit/861d5cb3d67f42baadf688e77ef3bece616e1259))
* **ReflectionUtil:** implement support for properties in parseType() ([b7b82cc](https://github.com/labor-digital/typo3-better-api/commit/b7b82cc31132b3915df3116b12302e1fe9b26dff))
* **TypoCoreConfigurator:** add getter and removal methods for existing options ([08e1749](https://github.com/labor-digital/typo3-better-api/commit/08e17493ba8fd6ba052d4fbc7890f7a59f743c99))
* implement SerializerUtil ([12528c0](https://github.com/labor-digital/typo3-better-api/commit/12528c0a64b7de0ea280f2e839f5c72ef2dbfa35))
* implement site based namespace convention ([5e947c1](https://github.com/labor-digital/typo3-better-api/commit/5e947c1cf0254fb45e6b2e5e936012867c308877))


### Bug Fixes

* **AbstractElementConfigurator:** throw invalid argument exception instead of ext config exception ([02c1477](https://github.com/labor-digital/typo3-better-api/commit/02c147790a50e939e202ca467cc6d7f65e0cdc8a))
* **SiteConfigAwareTrait:** register correct property to store the cached value ([b741f94](https://github.com/labor-digital/typo3-better-api/commit/b741f94df868148d6bcab2dc54db6322a17a8446))
* **TypoScript:** rename the extBase TS more generic ([d89554f](https://github.com/labor-digital/typo3-better-api/commit/d89554f9672c5705aaf4b830caa5090041a9d142))
* add missing renamed file references to previous commits ([09ab35a](https://github.com/labor-digital/typo3-better-api/commit/09ab35a653ddaaca8d92580eaa9b439b1d53605b))
* **ContentType\DataHandlerAdapter:** don't fail if the history record does not exist ([148d5d6](https://github.com/labor-digital/typo3-better-api/commit/148d5d608f593075e702438cdde344a593745be4))
* **ContentType\FieldAdapter:** remove unnecessary NoDiInterface ([d8da9f9](https://github.com/labor-digital/typo3-better-api/commit/d8da9f9a30cc7f6157cc856f927bf8e4c5bb0c38))
* **EnvironmentSimulator:** reimplement bootTsfe and make it more intuitive ([b2c9589](https://github.com/labor-digital/typo3-better-api/commit/b2c9589d95becd05a2b25eba7fac907cdad3d388))
* **StandaloneBetterQuery:** avoid using deprecated fetch methods ([d6d771f](https://github.com/labor-digital/typo3-better-api/commit/d6d771f974fa48b987eba8c8ee536b0e437a41e2))
* **Tca\AbstractTypeList:** lookup types type safe in hasType() ([2f59c3d](https://github.com/labor-digital/typo3-better-api/commit/2f59c3de46b5ca3f77af1beb8dc03c2ec66551e1))
* **Tca\DumperGenericTrait:** drop default "untitled" tab at beginning of showitem string ([10df82f](https://github.com/labor-digital/typo3-better-api/commit/10df82fd64acb966878e2a073b8ab2618b3666b9))
* **Tca\FactoryPopulatorTrait:** always create inferred tab if not target is present ([2f99c2a](https://github.com/labor-digital/typo3-better-api/commit/2f99c2a75b5f6afd7768ab0bef010152e5472ec8))
* **TcaTable:** implement temporary hotfix to retrieve loaded tca types in getTypeNames() ([4457d93](https://github.com/labor-digital/typo3-better-api/commit/4457d9370d3e7b1102e29d36010e25c443905070))
* **TcaTableType:** getField() now checks the default type name type save ([a0622a3](https://github.com/labor-digital/typo3-better-api/commit/a0622a3ba3cd6ed64a0b96cb55cd070f6ddef338))
* **TypoScriptService:** remove deprecated 'ignoreIfFrontendExists' option in environment simulator ([65777a0](https://github.com/labor-digital/typo3-better-api/commit/65777a04b767e0cf0c2e6bac0e75ddb175cb660f))

## [10.0.0-beta.9](https://github.com/labor-digital/typo3-better-api/compare/v10.0.0-beta.8...v10.0.0-beta.9) (2021-06-02)


### Features

* **ExtConfig:** implement output of extConfig state into lowLevel extension ([2009735](https://github.com/labor-digital/typo3-better-api/commit/2009735c2e9fb575fbb2ea0f742715c89e644745))
* **ExtConfigService:** implement resolveNamespaceForClass() ([41dff95](https://github.com/labor-digital/typo3-better-api/commit/41dff95115493be4de714c431811b7b81317f32f))
* **Link:** remove deprecated link properties ([45ce447](https://github.com/labor-digital/typo3-better-api/commit/45ce447118879bcc90849549fff94d209d66a43a))
* **RequestCacheKeyGenerator:** add $trackedHeaders and $excludedQueryParams options ([cbf93b7](https://github.com/labor-digital/typo3-better-api/commit/cbf93b742282d9d8b19fd4cbdc753bdc1f009b72))
* **Simulation:** fix deprecation issues in TsfeSimulationPass ([d8e7287](https://github.com/labor-digital/typo3-better-api/commit/d8e728709bb5c8ad7bc6174feb3d74199426ba75))
* **Simulation:** remove deprecated runAsAdmin method ([6e258fb](https://github.com/labor-digital/typo3-better-api/commit/6e258fbecd3a044389c392cacc858dfe00a796f7))
* **Simulation:** remove tsfeService dependency in EnvironmentSimulator ([6ad37a7](https://github.com/labor-digital/typo3-better-api/commit/6ad37a799e5aa80512da852d44c22cc90806f7f1))
* **TcaTable:** implement registration for CSH labels ([71f6f73](https://github.com/labor-digital/typo3-better-api/commit/71f6f73119e990b76d6a9b900959fe02b825ee57))
* **Translation:** allow runtime label overrides ([04bce92](https://github.com/labor-digital/typo3-better-api/commit/04bce92f026e169ae403906b3e7717212a114546))
* **TypoContext:** add "date" aspect getter ([11559fb](https://github.com/labor-digital/typo3-better-api/commit/11559fb16e230672dac2116dbdfe6e6772bd1e0e))
* merge CodeGenerationHelperTrait into ReflectionUtil ([0bd9e8e](https://github.com/labor-digital/typo3-better-api/commit/0bd9e8eb903a28b8e6a067a15b870e69653fed25))


### Bug Fixes

* **AbstractExtendedCache:** enable useEnvironment as a default ([27e114d](https://github.com/labor-digital/typo3-better-api/commit/27e114dc4cadaaf1ccc0a94c770babf568fffa00))
* **AbstractExtendedCache:** fix some issues with "enabled" and "lifetime" calculation ([77cabc5](https://github.com/labor-digital/typo3-better-api/commit/77cabc51cf9661e3ca9ed961ab291427f0908131))
* **Cache:** harden cache tag generation ([2ee4879](https://github.com/labor-digital/typo3-better-api/commit/2ee487934965013ec47ae7ba98d1527bab631a15))
* **Cache\Implementation:** add $key argument to all hook methods ([2203470](https://github.com/labor-digital/typo3-better-api/commit/22034706f2d5df3c52365221e8c077d6e4484545))
* **Cache\KeyGenerator:** add NoDiInterface to key generators ([e07b6cb](https://github.com/labor-digital/typo3-better-api/commit/e07b6cb9561e697d669e34052d17156cc6f0fd3e))
* **CacheConfigurationPass:** pass the correct array of cache key enhancers ([1138dca](https://github.com/labor-digital/typo3-better-api/commit/1138dca3854d3bea8abcd437bbcc473873efae41))
* **Controller:** make BetterContentActionController abstract ([3be2fc2](https://github.com/labor-digital/typo3-better-api/commit/3be2fc294668dd3c2bd1870e7723136024bb4f6d))
* **ExtendedCacheManager:** use flushCachesInGroupByTags instead of flushCachesInGroupByTag ([e606542](https://github.com/labor-digital/typo3-better-api/commit/e606542a0789dc958b0e7fe71852f90deff070c0))
* **FieldPreset:** add correct eval rule to date field ([9a8933e](https://github.com/labor-digital/typo3-better-api/commit/9a8933e5e910ba86aa41c2399ae4c42ea37b8855))
* **NamingUtil:** make missing method exception more speaking ([bec144a](https://github.com/labor-digital/typo3-better-api/commit/bec144aaade463934b1a1d801b9455e8304257b8))
* **TcaUtil:** convert the array fields more reliable in getRecordType() ([aae38f8](https://github.com/labor-digital/typo3-better-api/commit/aae38f894d6bfd1e657ad13feed79acc7b076bff))
* **Translation:** store translator instance locally in TranslationLabelProvider ([abfe9ed](https://github.com/labor-digital/typo3-better-api/commit/abfe9ed1f8be705f9a63314ddec07ed784f298fe))
* **TranslationConfigurator:** persist overrides without a namespace ([e658111](https://github.com/labor-digital/typo3-better-api/commit/e65811183b6470627732a700f02d006e2c77b8d6))
* **VarFs\Mount:** retry if mountPath could not be removed ([41e3e52](https://github.com/labor-digital/typo3-better-api/commit/41e3e52f50bb10bda8eb23069f08557519a40bc2))

## [10.0.0-beta.8](https://github.com/labor-digital/typo3-better-api/compare/v10.0.0-beta.7...v10.0.0-beta.8) (2021-05-26)


### Features

* **ExtConfig\TypoScript:** set default paths for page and user ts imports ([8612b49](https://github.com/labor-digital/typo3-better-api/commit/8612b49e00f043b1f71060d12ac9f19d0c7aacf6))


### Bug Fixes

* **ExtConfig\TypoScript:** set CORRECT default paths for page and user ts imports ([55019cb](https://github.com/labor-digital/typo3-better-api/commit/55019cbee62c83150512f4c9407a607ca48fa451))

## [10.0.0-beta.7](https://github.com/labor-digital/typo3-better-api/compare/v10.0.0-beta.6...v10.0.0-beta.7) (2021-05-26)


### Bug Fixes

* **RoutingConfigurator:** convert middleware classes to identifiers in "before" & "after" ([ffd91f4](https://github.com/labor-digital/typo3-better-api/commit/ffd91f4bc2e0efb0ccce2e8ffeff719ea05db414))

## [10.0.0-beta.6](https://github.com/labor-digital/typo3-better-api/compare/v10.0.0-beta.5...v10.0.0-beta.6) (2021-05-17)


### Features

* **TypoContext:** make registration of facets dynamic, so extensions can add their own ([0d52464](https://github.com/labor-digital/typo3-better-api/commit/0d524648af4065ab21e5cc5891202e09f41263be))

## [10.0.0-beta.5](https://github.com/labor-digital/typo3-better-api/compare/v10.0.0-beta.4...v10.0.0-beta.5) (2021-05-16)


### Features

* implement NoDiInterface and NonSharedServiceInterface ([ad5d4e9](https://github.com/labor-digital/typo3-better-api/commit/ad5d4e9daf7c5f6a389c993cf7e7760a69ae3e49))


### Bug Fixes

* **CodeGenerationHelperTrait:** remove unwanted leading slash in parseType() ([575ec5c](https://github.com/labor-digital/typo3-better-api/commit/575ec5c048f86524e0502ecb6e7ba0a92e284ddc))
* **PidFacet:** don't break when feature is not enabled ([dc1d9db](https://github.com/labor-digital/typo3-better-api/commit/dc1d9db40b36fd1d2a41784037e9bccd72d4f3ff))

## [10.0.0-beta.4](https://github.com/labor-digital/typo3-better-api/compare/v10.0.0-beta.3...v10.0.0-beta.4) (2021-05-03)


### Bug Fixes

* fix composer v2 issues ([5b5776b](https://github.com/labor-digital/typo3-better-api/commit/5b5776b57129174cb75a11a723cb2cf9e057c608))

## [10.0.0-beta.3](https://github.com/labor-digital/typo3-better-api/compare/v10.0.0-beta.2...v10.0.0-beta.3) (2021-05-03)


### Bug Fixes

* **ContentTypeUtil:** runWithRemappedTca() will no longer fail if CType could not be resolved ([b88ae38](https://github.com/labor-digital/typo3-better-api/commit/b88ae38b359de5c1dd94f18b6315bfd44a9d9bb4))
* **ExtConfig\Fluid:** generate viewHelper key as camelBack instead of CamelCase ([6efc8f5](https://github.com/labor-digital/typo3-better-api/commit/6efc8f52acaa7b25af3ffb23d075c667aeb8a5e3))
* **ExtConfig\Table:** harden new table name resolution ([1127288](https://github.com/labor-digital/typo3-better-api/commit/1127288f26a8eee87df337e975a863a7241b91b0))

## [10.0.0-beta.2](https://github.com/labor-digital/typo3-better-api/compare/v10.0.0-beta.1...v10.0.0-beta.2) (2021-05-03)


### Bug Fixes

* multiple fixes after namespace change ([a6f044a](https://github.com/labor-digital/typo3-better-api/commit/a6f044adb9925d70972323a5c4f407ea2be2bdf6))

## [10.0.0-beta.1](https://github.com/labor-digital/typo3-better-api/compare/v9.20.0...v10.0.0-beta.1) (2021-05-03)


### Features

* enter "beta" stage ([3b9f6e9](https://github.com/labor-digital/typo3-better-api/commit/3b9f6e9e2a7fb594bbb4e87d9bd8de30f2bb543c))
* make new extension key and namespace compatible with TER ([d77b984](https://github.com/labor-digital/typo3-better-api/commit/d77b984a6876c9937fdb512937fb3a1ece86d0be))
* **AbstractField:** add setDefault() to form fields | from: b45402c1 ([946f135](https://github.com/labor-digital/typo3-better-api/commit/946f135abd706d0a8e043763ce2e89e3f33c6025))
* **BetterLanguageAspect:** getAllFrontendLanguages() can now take a siteIdentifier ([ee625f7](https://github.com/labor-digital/typo3-better-api/commit/ee625f72b4f4c02595a22841ec2764e8de21e4da))
* **BetterLanguageAspect:** implement site switching and getLanguageById() ([1855cbf](https://github.com/labor-digital/typo3-better-api/commit/1855cbff590d82b0d7bdbc89932aa79d377ddedd))
* **Cache:** finalize CacheInterface ([7e1e6a9](https://github.com/labor-digital/typo3-better-api/commit/7e1e6a929459cfa34c5940c337ca5d1706b1a273))
* **CodeGenerationHelperTrait:** implement parseType() for PHP8 compatible type handling ([a3ba96a](https://github.com/labor-digital/typo3-better-api/commit/a3ba96a69aa93980264a7ef52693733440e6eb73))
* **extConfig:** split up ext base content element config ([2391615](https://github.com/labor-digital/typo3-better-api/commit/23916155ea6bc5c776cd47d357466e8ff068eaea))
* **ExtConfig:** implement "raw" handler ([4a0ad56](https://github.com/labor-digital/typo3-better-api/commit/4a0ad564707bd6a7834ccd58e091e5d993c18bc8))
* **ExtConfig:** implement asset registration for frontend ([0ec8479](https://github.com/labor-digital/typo3-better-api/commit/0ec847977120509e569883e369fb752a87dec9a2))
* **ExtConfig:** use the frontend asset registration syntax for the backend, too ([1a89a35](https://github.com/labor-digital/typo3-better-api/commit/1a89a35a9c460529f65b194a21d747332a0eac84))
* **FileRenderer:** implement support for sys_language_uid ([c9099ce](https://github.com/labor-digital/typo3-better-api/commit/c9099cedf3ccc7dbc425cccdf3ddcacc0a8657a2))
* **Log:** implement StreamWriter and BeLogWriter + global logging option ([eeaa38c](https://github.com/labor-digital/typo3-better-api/commit/eeaa38c143e2fc808fa70298a766bf75c0478762))
* **PathFacet:** make getSlugFor() work again ([725c11d](https://github.com/labor-digital/typo3-better-api/commit/725c11dcad394463f0b5a0966fcdc7fadff840ca))
* **ReflectionUtil:** implement getClosestFromStack() method ([6b3977d](https://github.com/labor-digital/typo3-better-api/commit/6b3977d177249394629b491914046fb89560246a))
* **RelationPreset:** add additional options ([733ac1a](https://github.com/labor-digital/typo3-better-api/commit/733ac1a3fe7b42fcc7a9df53148fd9d51e644bb3))
* **Tca:** add removeChildren() for bulk removal ([d898ac0](https://github.com/labor-digital/typo3-better-api/commit/d898ac0f08fb263844e326c8d61538debf5725e4))
* **Translator:** implement translateBe() ([51738a5](https://github.com/labor-digital/typo3-better-api/commit/51738a5cde9c9547bbfd5dd4b5a65d76d8ed9b1e))
* code cleanup and boot time optimization ([36d24ce](https://github.com/labor-digital/typo3-better-api/commit/36d24ce8f84503e48c3a1222654e48d5893cf8f9))
* **ExtConfig:** implement DefaultDependencyInjectionConfig.php to make the setup in extensions easier ([1dbcc77](https://github.com/labor-digital/typo3-better-api/commit/1dbcc77db652f1671d4bf926c165020eb224ef7c))
* **NamingUtil:** implement v9 adjustments ([5b9b2c9](https://github.com/labor-digital/typo3-better-api/commit/5b9b2c9f97d07329119f31a68ad93367ffccdb91))
* finish migrating better query ([850b655](https://github.com/labor-digital/typo3-better-api/commit/850b655d82531413f80cf9aea635a2f5226c48a1))
* finish migrating extended relation resolution ([2191b13](https://github.com/labor-digital/typo3-better-api/commit/2191b139b343f6adae188c6779c55e96c6026bfd))
* finish up most of the migration work and begin cleanup ([592d407](https://github.com/labor-digital/typo3-better-api/commit/592d40750c010367ba2095e2256fcdc85f997713))
* implement content type handling (FEApi - VCols) ([302dc5f](https://github.com/labor-digital/typo3-better-api/commit/302dc5fdc309fae99baf3bec5d63930785176a06))
* implement data hooks as successor of backend actions handler ([690c121](https://github.com/labor-digital/typo3-better-api/commit/690c121d9da23d44a931842e1b6b5994ea84c4ec))
* implement FieldPresetFilterEvent event ([4eff65c](https://github.com/labor-digital/typo3-better-api/commit/4eff65c0b77dc98850062b4d59b83084bb51d099))
* implement page based config + routing config ([1874a59](https://github.com/labor-digital/typo3-better-api/commit/1874a5994d298e83534338a1b76ec2668478cc8b))
* implement remaining TCA migrations and fix all data handler related issues ([14eb2c4](https://github.com/labor-digital/typo3-better-api/commit/14eb2c4d61e347917ac63dd31f7c51b9a098a920))
* implement SingletonInstanceTrait.php ([7409360](https://github.com/labor-digital/typo3-better-api/commit/7409360719366da3e3fc9810850ede486be8f9f1))
* implement tca utility ([3c9dc9e](https://github.com/labor-digital/typo3-better-api/commit/3c9dc9e4b0c3b7af9614de8e2dca09d3ad363f7a))
* implement tsConfig lookup to typoScript service and configFacet ([275adc5](https://github.com/labor-digital/typo3-better-api/commit/275adc5e1f9e66afb642fd70ca74b07d7eb3d7b6))
* inline field preset + plugin flex forms + backend rendering ([7286e2e](https://github.com/labor-digital/typo3-better-api/commit/7286e2ea2bd7baf04f2c2d3dec0a8fd7a4e4a01b))
* make getParent() or getParentContext() methods more speaking ([a0236ca](https://github.com/labor-digital/typo3-better-api/commit/a0236caa7908421e34ea87632783914142f94d07))
* migrate backend preview helpers ([8acf6b5](https://github.com/labor-digital/typo3-better-api/commit/8acf6b5dd08f9e33be04dba07efd7b26648220b8))
* migrate BackendRenderingService ([9c6bb58](https://github.com/labor-digital/typo3-better-api/commit/9c6bb58ae2f6b858110c9f15e029c649d3693b90))
* migrate fluid config option ([34aee06](https://github.com/labor-digital/typo3-better-api/commit/34aee06f3f557ea68aee9b4fb913202f1b770ecb))
* migrate form filter to data hook ([3547260](https://github.com/labor-digital/typo3-better-api/commit/3547260007ca94151d9844e47d5d33c2af38cc79))
* migrate kernel to new singleton interface ([e44b6b2](https://github.com/labor-digital/typo3-better-api/commit/e44b6b2b5b5ace40841be547200fccf06f0eb2fa))
* migrate log configuration ([f2d131f](https://github.com/labor-digital/typo3-better-api/commit/f2d131f30fd6fa855c105a5dc95c2df51cc6827f))
* migrate most of the extbase better query logic ([e8da488](https://github.com/labor-digital/typo3-better-api/commit/e8da4885213907ff32a99db1fae26ff9143fee26))
* migrate most of the flex form logic + implement better table sql builder ([0621f5d](https://github.com/labor-digital/typo3-better-api/commit/0621f5dc01dfde641c657206b179a0e93cf5cbbc))
* migrate most of the tca abstraction to the new version ([3e90752](https://github.com/labor-digital/typo3-better-api/commit/3e90752ef49787cd86e98cb61085c911ea271630))
* migrate multiple link related features + general bugfixes and adjustments ([a475bb5](https://github.com/labor-digital/typo3-better-api/commit/a475bb5b4ca40e67c63a703dbee1d5666c6724d1))
* migrate name space references to T3BA ([5f14c42](https://github.com/labor-digital/typo3-better-api/commit/5f14c427e2303134c1c7c988da74a0f30166db90))
* migrate rendering tools ([39ea0f7](https://github.com/labor-digital/typo3-better-api/commit/39ea0f7d47bd7aedbfe5ee765047e5aed22c2f3d))
* re-implement caching logic ([37bf2ae](https://github.com/labor-digital/typo3-better-api/commit/37bf2ae878a78eec7d05f6309df7855493139a81))
* rework the boot process to fix extension installation issues ([5537b22](https://github.com/labor-digital/typo3-better-api/commit/5537b22bb2057c897fd76943fce2358039544409))
* simplify namespace and class names + varFs optimization for less disk operations ([e531978](https://github.com/labor-digital/typo3-better-api/commit/e5319786ea8d508c3c8639627cd33004654370c7))
* unify getParentContext() methods ([a86b007](https://github.com/labor-digital/typo3-better-api/commit/a86b007b67bff01cf1e393a6a653ce73ccb53299))
* update eventBus package to v3.0.0 ([dedd756](https://github.com/labor-digital/typo3-better-api/commit/dedd756997f5809b8e8261fbd2cec48fab27827a))
* **BetterQuery:** begin migrating BetterQuery ([22366d3](https://github.com/labor-digital/typo3-better-api/commit/22366d39d60c34add14dd11c22c7e9e45679fedd))
* **CommonDependencyTrait:** add DataHandler and Session services ([d78172b](https://github.com/labor-digital/typo3-better-api/commit/d78172bd26005ffc865f8ffd850e90c897cfcab1))
* **CommonDependencyTrait:** add fal service ([787d610](https://github.com/labor-digital/typo3-better-api/commit/787d6100c5b324c952f8c75fb786de3326c00c2b))
* **Database:** migrate database service ([56137c8](https://github.com/labor-digital/typo3-better-api/commit/56137c8d4c2eb0d4bc081190701d8c090c1a8c6e))
* **DataHandler:** implement DataHandlerService and RecordDataHandler ([0746d65](https://github.com/labor-digital/typo3-better-api/commit/0746d6571a7eeb99d3aeace362a02003bbace882))
* **DependencyInjection:** add hasLocalSingleton() to ContainerAwareTraits ([94947e9](https://github.com/labor-digital/typo3-better-api/commit/94947e9a5372172e603f4f680bbd1b38cb3028a2))
* **ExtConfig:** add key definition handling ([b5a4b8a](https://github.com/labor-digital/typo3-better-api/commit/b5a4b8ab77076b0d982d07488e0159e87abfa3e0))
* **ExtConfig:** create group ext config abstract ([9510c0f](https://github.com/labor-digital/typo3-better-api/commit/9510c0fb220f46a63f1fbb2765d05406c0e1b080))
* **ExtConfig:** make the context more general purpose by implementing a shared trait ([5935dcc](https://github.com/labor-digital/typo3-better-api/commit/5935dcc104986eda4dc720fb1ad2a75058baa5de))
* **ExtConfig:** migrate ext base plugin configuration ([85fb4cb](https://github.com/labor-digital/typo3-better-api/commit/85fb4cb0b5367a8902e7d39bc3ba8b72f0969087))
* **FlashMessageRenderingService:** implement better queue detection for extbase controllers ([dd96792](https://github.com/labor-digital/typo3-better-api/commit/dd96792c7d2ddee5f0a04b9c1e2ff6be7cbcacc2))
* **Page:** migrate page service ([de01b3c](https://github.com/labor-digital/typo3-better-api/commit/de01b3c9782a332fa04b71a02c06af42211e1b2f))
* **PageService:** use RecordDataHandler to perform the page actions ([e4c4845](https://github.com/labor-digital/typo3-better-api/commit/e4c48457602d27853ab36178806dc9de68909cd4))
* **PidFacet:** use LocallyCachedStatePropertyTrait to sync pids ([591a6d9](https://github.com/labor-digital/typo3-better-api/commit/591a6d9bfa17da75899d53e4774159f001526857))
* **Simulation:** implement v9 bugfixes + performance improvements ([536d7e1](https://github.com/labor-digital/typo3-better-api/commit/536d7e19f879866393f85ee50415ce5354d78128))
* **TypoContext:** add dependency injection facet ([3fcca89](https://github.com/labor-digital/typo3-better-api/commit/3fcca8999b1f81ed37d18bbbc9c3aa464f54a528))
* **TypoContext:** add getExtensionIconPath() to PathFacet ([4a47bb0](https://github.com/labor-digital/typo3-better-api/commit/4a47bb06ef290632323653ce34d90b45b67e6128))
* **TypoContextAwareTrait:** try to resolve the typo context using the container first ([5fbf4ad](https://github.com/labor-digital/typo3-better-api/commit/5fbf4ad05b29bdb895199bc62c9a50bbd5324517))
* begin to migrate ext-config options ([2c2afff](https://github.com/labor-digital/typo3-better-api/commit/2c2afff52a3dd6da2d35eb954ad70fcba6f54e9a))
* begin to migrate typoScript ext config ([cbaf6e9](https://github.com/labor-digital/typo3-better-api/commit/cbaf6e9bbc37a4cb618050f12c73fbe637aa4607))
* finish implementing kernel and dependency injection configuration ([0623ce0](https://github.com/labor-digital/typo3-better-api/commit/0623ce09e78dde97c96839e1e901afd51130e230))
* implement hook package registration to new kernel ([0e9d903](https://github.com/labor-digital/typo3-better-api/commit/0e9d90316af09f55c93a52ef35a4d6cf36f9ab1a))
* implement new better api kernel ([60db5e8](https://github.com/labor-digital/typo3-better-api/commit/60db5e851742f8d49c06bdcff39e0a7fb5738a19))
* merge generic utilities under Tool\OddsAndEnds ([0665a27](https://github.com/labor-digital/typo3-better-api/commit/0665a272a618aca0a4e5f28f52775b3ae96fe04e))
* migrate ext base controller addons ([bd628f2](https://github.com/labor-digital/typo3-better-api/commit/bd628f2a2582d8783ece4b84422a1236e0c45bc3))
* migrate ext base module config handler ([cc7c861](https://github.com/labor-digital/typo3-better-api/commit/cc7c86184caf59b2f275043b3ffc01fc1d080de1))
* migrate fal service ([2f91d08](https://github.com/labor-digital/typo3-better-api/commit/2f91d08460c5d047864b35c098ecad4fe87cad98))
* migrate Kint debugger TYPO3 bridge ([d8bfdf8](https://github.com/labor-digital/typo3-better-api/commit/d8bfdf836b82bb3a9d357aacb77ee04c12f0fa9c))
* migrate lazy loading util ([75a0ac7](https://github.com/labor-digital/typo3-better-api/commit/75a0ac7a752dc2a85492832d8f73b36397795f5c))
* migrate link service ([159db1f](https://github.com/labor-digital/typo3-better-api/commit/159db1f6328ed21d3ba0737b5b2015a699f1140d))
* migrate more init work into the kernels boot stages ([5bb20f8](https://github.com/labor-digital/typo3-better-api/commit/5bb20f80e0a7c1c87303122965ebc3fbab61138f))
* migrate pid handling + bugfixes on facets and context ([4e8807e](https://github.com/labor-digital/typo3-better-api/commit/4e8807ea5c71e3e5993fd5a902dd09aeac173218))
* migrate request collector middleware ([7f5ef8e](https://github.com/labor-digital/typo3-better-api/commit/7f5ef8ebbe9081c2758d060f9d77b13472510159))
* move event classs into sub-namespaces ([57870ef](https://github.com/labor-digital/typo3-better-api/commit/57870efd8e1be550a1fb7870aaae4d4a5cfe2eb5))
* use GeneralUtility::getContainer() instead of makeInstance(ContainerInterface) ([e224f1e](https://github.com/labor-digital/typo3-better-api/commit/e224f1eef0dd1a3af529f60120c87f9c0e5e8c7f))
* **Session:** migrate session handling ([935c2b8](https://github.com/labor-digital/typo3-better-api/commit/935c2b8de011f3fff8fb686f84c0320e8de5adbb))
* **Simulation:** backup pid config before the simulation ([ab38cc7](https://github.com/labor-digital/typo3-better-api/commit/ab38cc7d749d14e18e9229d948a5fbc017581024))
* **Simulation:** migrate environment simulator ([6a3c712](https://github.com/labor-digital/typo3-better-api/commit/6a3c712f642476ca004666334355a470dfd86fcd))
* **Translation:** migrate translation service ([cf153a7](https://github.com/labor-digital/typo3-better-api/commit/cf153a78042424603932c5674e52040455c42b15))
* **Translator:** use LocallyCachedStatePropertyTrait to sync config ([cbc8925](https://github.com/labor-digital/typo3-better-api/commit/cbc892592e1dbb8abc886f29c4e6fe7f14178cf0))
* **Tsfe:** migrate tsfe service ([3d9e72e](https://github.com/labor-digital/typo3-better-api/commit/3d9e72efdbe96b0e091c8b0797ac0f12e857eb7b))
* **TypoContext:** implement TypoContextAwareTrait + static version ([2ab25e3](https://github.com/labor-digital/typo3-better-api/commit/2ab25e39d89ce26cd44d2a2ca743ab078624abf0))
* migrate TypoContext an additional TypoScript settings ([11f350e](https://github.com/labor-digital/typo3-better-api/commit/11f350e667aa05499ca08db3cd61c5c36bff9ac0))
* move ExtConfig related stuff into its own module ([541a1db](https://github.com/labor-digital/typo3-better-api/commit/541a1db5c2921280a73dca97fd5bd5ad5d1817d5))
* scrap the "module" approach and build a "sane" classes architecture instead ([0181a5c](https://github.com/labor-digital/typo3-better-api/commit/0181a5cfca61a1ca821060d889ea584c829eea46))


### Bug Fixes

* remove no longer required whitecube/lingua package ([dfc448f](https://github.com/labor-digital/typo3-better-api/commit/dfc448f521a07c8c67906e3cf7973de54ea71482))
* **AbstractActionEvent:** handle "pasteSpecialData" as "mixed" data type ([d634773](https://github.com/labor-digital/typo3-better-api/commit/d6347735800667ab154aee070c395e7d5d99aa36))
* **CacheConfiguratorPass:** prefer using CodeGenerationHelper for type parsing ([ef05c4c](https://github.com/labor-digital/typo3-better-api/commit/ef05c4ccb770c5ac21b0ad75e02003cec001837b))
* **CodeGenerationHelperTrait:** make sure return type is generated correctly ([55222ba](https://github.com/labor-digital/typo3-better-api/commit/55222ba527c442e2eb588893d62665935631693e))
* **ConfigArrayPostProcEventAdapter:** actually modify the parameter array ([7aa7eec](https://github.com/labor-digital/typo3-better-api/commit/7aa7eec52b2dc56285fee3f0675b6addcdcccdc6))
* **ConfigureFrontendInterface:** make configureFrontend() site context aware ([a03b8b7](https://github.com/labor-digital/typo3-better-api/commit/a03b8b705b1114f34a170af9a8e0304e4c4a59d4))
* **ContentType:** don't reexecute loader in cli or install tool ([fdfce71](https://github.com/labor-digital/typo3-better-api/commit/fdfce717c677928db28b73e42c2a84accd3fdde0))
* **Custom\Field:** finish implementing CustomFieldDataHookContext ([1a855a0](https://github.com/labor-digital/typo3-better-api/commit/1a855a09c1043c44451c7386250e5d56a5b2132e))
* **Custom\Field:** fix context type annotation for applyCustomElementPreset() ([7b5830c](https://github.com/labor-digital/typo3-better-api/commit/7b5830c9e7821a41320b59b7ce0ffae9c4a767fd))
* **Custom\Field:** fix data hook option generation in CustomFieldPresetTrait ([f3fc307](https://github.com/labor-digital/typo3-better-api/commit/f3fc307c480ff885443bec6440283a56cf662263))
* **DataHook:** finish implementing FlexFormFieldPacker ([4175408](https://github.com/labor-digital/typo3-better-api/commit/4175408c903556a219ce4ee16521029dc92f6910))
* **DbService:** use NamingUtil::resolveTableName in getQueryBuilder() ([f8ac6e6](https://github.com/labor-digital/typo3-better-api/commit/f8ac6e64e7ea4a3aba86978657e77c2e67bf2c6b))
* **DelegateContainer:** set return time of get() to "mixed" ([1c8544f](https://github.com/labor-digital/typo3-better-api/commit/1c8544ff4c5c51ce7c49f9e79f4806bb13177da3))
* **DisplayConditionTrait:** make array based condition handling more reliable ([d5aaa4e](https://github.com/labor-digital/typo3-better-api/commit/d5aaa4e3c6b962378bc64de618055f2371a340d6)), closes [#43b0c52](https://github.com/labor-digital/typo3-better-api/issues/43b0c52)
* **ExtConfig:** correctly apply log configuration ([8951d2b](https://github.com/labor-digital/typo3-better-api/commit/8951d2b2029815a68aa7c6c645265d9662d563c8))
* **ExtConfig:** rename ConfigureTypoScriptInterface::configure to configureTypoScript ([4513f50](https://github.com/labor-digital/typo3-better-api/commit/4513f50dd28b9e9d2028172d69971f4ebb62f344))
* **ExtConfig\SiteBased:** make sure site based config does not get overwritten ([c3adc67](https://github.com/labor-digital/typo3-better-api/commit/c3adc6755f1d6e2c12a0347a04fe372d5ddd17c1))
* **ExtConfig\Table:** decouple table name generation from extKey / namespace ([561ce28](https://github.com/labor-digital/typo3-better-api/commit/561ce284009526c89ba640b242a4e55c89e9d1a7))
* **ExtConfig\TypoScript:** rename ConfigureTypoScriptInterface::configure to configureTypoScript ([16288ad](https://github.com/labor-digital/typo3-better-api/commit/16288ad49d4b285adb861f6b0bd4ac19b914e0e6))
* **ExtendedSiteConfiguration:** don't pollute the site config yml files when saving the min the backend ([b0c568b](https://github.com/labor-digital/typo3-better-api/commit/b0c568be9e2817b2f997653be4931032e7ee1c48))
* **Fal:** generate file urls correctly even with external FAL drivers | from: c60f03c1 ([df4c571](https://github.com/labor-digital/typo3-better-api/commit/df4c571c9438d163c1092c1a91947942b43ab369))
* **Link:** finish implementing cacheHash disabling on links ([94f0655](https://github.com/labor-digital/typo3-better-api/commit/94f065506a0652c0cb16ca5e77417b0e079bbd3d))
* **Link:** remove no longer required "else" when no language is selected ([5225508](https://github.com/labor-digital/typo3-better-api/commit/52255082e22a22004d2de95f21aec6eaaf15b137))
* **LinkService:** handle "fragment:" prefix in $args of getLink() ([c19c179](https://github.com/labor-digital/typo3-better-api/commit/c19c17999642c4ec5aae3d937f31ae40c5742a8d))
* **RoutingConfigurator:** remove unused $site property ([f04eb16](https://github.com/labor-digital/typo3-better-api/commit/f04eb169a8a3ed65ec7d6fed96555b2a68209407))
* **SiteFacet:** make sure "NullSites" are ignored correctly ([3c2781f](https://github.com/labor-digital/typo3-better-api/commit/3c2781fac99d676276221341f36ab93b5f5f608a))
* **StandaloneBetterQuery:** handle empty results in getRelatedRecords correctly ([04cdd15](https://github.com/labor-digital/typo3-better-api/commit/04cdd1508d3342031ef00c79131e94963450fcc2))
* **TypoScriptService:** finish implementing renderContentObject ([2800106](https://github.com/labor-digital/typo3-better-api/commit/2800106a06fea443b866d71b12777123e07eb80a))
* **TypoScriptService:** fix minor bug in TypoScriptConfigurationManager ([9a3a39b](https://github.com/labor-digital/typo3-better-api/commit/9a3a39bf89dbcba5fcb71832a660a6cd2964599d))
* **TypoScriptService:** load tsConfig from TSFE if possible ([a5bdb7c](https://github.com/labor-digital/typo3-better-api/commit/a5bdb7cc01656efc5cdd05d0663a51faa614dd02))
* **VarFs:** only remove the temp fs cache data if the "all" cache is cleared without any tags | from: 8daf4de9 ([96a490e](https://github.com/labor-digital/typo3-better-api/commit/96a490e0bfdc66de953202294693bcecd0527928))
* fix code a multitude of minor issues and weak warnings ([f71f101](https://github.com/labor-digital/typo3-better-api/commit/f71f10135806c8997dfd733660c34209ad6a6ee8))
* harden ext config di loading process ([8059c3d](https://github.com/labor-digital/typo3-better-api/commit/8059c3dace7b27298a479747b8f7abf5bf5bb7b9))
* **BackendPreview:** extend image file rendering ([f8cdc86](https://github.com/labor-digital/typo3-better-api/commit/f8cdc868fec82c9b504143c64ae236a7b3094182))
* **BackendPreview:** make config generation more reliable ([7170967](https://github.com/labor-digital/typo3-better-api/commit/7170967359111d4fc55b399d0cae1ffddd70e4b5))
* **BetterQuery:** allow whereUidSpecialConstraintWrapper to return strings ([831db88](https://github.com/labor-digital/typo3-better-api/commit/831db88813a979ce00f9fbb753ecdeff10e5427d))
* **BootStage:** make sure ConfigState is available at all times ([4d0efd3](https://github.com/labor-digital/typo3-better-api/commit/4d0efd3fea5156314188c10a35da975b4ff3b2fe))
* **CacheClearedEvent:** make sure to emit event on all required methods ([44f8647](https://github.com/labor-digital/typo3-better-api/commit/44f8647769eaa278602a799b3b371ce97317a6dd))
* **CommonDependencyTrait:** make Session() and DataHandler() protected ([6ed65f7](https://github.com/labor-digital/typo3-better-api/commit/6ed65f7281ef5d9e78afafd68939ee422688367e))
* **ContainerAwareTrait:** rename injectContainer() to setContainer() to fix extBase issues ([e8dcc47](https://github.com/labor-digital/typo3-better-api/commit/e8dcc470abab278e5637a1f6ab03bfe3ad610c3e))
* **ContentType:** harden ContentTypeUtil against array 'cType' entries ([4cd2463](https://github.com/labor-digital/typo3-better-api/commit/4cd2463fac3b60b5c5dacf6a5406da690f5b7185))
* **DataHandler:** auto-login when used in cli ([d22d8b3](https://github.com/labor-digital/typo3-better-api/commit/d22d8b3c8a40e9617836eb9be1427405d47c5a7d))
* **DelegateContainer:** fix container issue in install tool ([1b2765a](https://github.com/labor-digital/typo3-better-api/commit/1b2765a3410ed7ead9ebfe94a6c88d0cfe1233d2))
* **DependencyInjection:** remove unnecessary singleton update in getInstanceOf() ([0adbda3](https://github.com/labor-digital/typo3-better-api/commit/0adbda351663b92927175b5dcf67a13ba4be0931))
* **EventBus:** simplify class implementation ([0a52ce9](https://github.com/labor-digital/typo3-better-api/commit/0a52ce96a3c94ba51ad8fa7e48e19bbb5ecf8e27))
* **ExtBase:** finish migrating action controller ([c71e319](https://github.com/labor-digital/typo3-better-api/commit/c71e31941594537a330271585861c454cb0f8a7e))
* **ExtConfig:** fix bugs in plugin config generation ([9ece726](https://github.com/labor-digital/typo3-better-api/commit/9ece726733e9336fe528c4e1994df41617eb9930))
* **ExtConfig:** mark handler abstracts as public services ([e6adc92](https://github.com/labor-digital/typo3-better-api/commit/e6adc92b7d2d612198a2970a86d5e3cfcafe2bc5))
* **ExtConfigEventHandler:** remove dev fractal ([d64600c](https://github.com/labor-digital/typo3-better-api/commit/d64600cc1df6d616a70e60b2e64a23a657e940f2))
* **FalService:** fix type error when using integer as uid ([10e917d](https://github.com/labor-digital/typo3-better-api/commit/10e917dd030d6efd7bb7bac062b8de543aee5107))
* **Fluid:** fix incorrect view helper configuration ([18e533e](https://github.com/labor-digital/typo3-better-api/commit/18e533e6766d542db24aa9b16f56e908d159cff7))
* **Link:** add missing allowLinkSets option to field preset ([f6ad8bc](https://github.com/labor-digital/typo3-better-api/commit/f6ad8bc94268f5d63fbdc74903ac327506e0578f))
* **LinkContext:** create the extbase request as local singleton ([a16d3a1](https://github.com/labor-digital/typo3-better-api/commit/a16d3a16977bf38d9861caa74b32a6c40988228d))
* **PageService:** implement v9 bugfix for root line generation ([92e08e3](https://github.com/labor-digital/typo3-better-api/commit/92e08e30bf80faa4ec03ac48909a832806672955))
* **RecordDataHandler:** use correct "undelete" command ([33134d1](https://github.com/labor-digital/typo3-better-api/commit/33134d1190e4446c714e7317e5edf35fdd1d0d02))
* **Simulation:** mark simulator passes as public ([58c0ec1](https://github.com/labor-digital/typo3-better-api/commit/58c0ec1cb6f7663f1bbd5c8d8f22f4fd93b4da26))
* **Tca:** avoid dumping showitem without sensible content ([56169c0](https://github.com/labor-digital/typo3-better-api/commit/56169c0421f6469038707831634385e3a312f371))
* **Tca:** getSortedNodes() now returns children in palettes ([fb0997d](https://github.com/labor-digital/typo3-better-api/commit/fb0997daa13460e0a67254ec2c4af3da71bdf03b))
* ExtendedNodeFactory now extends the correct parent ([3f28580](https://github.com/labor-digital/typo3-better-api/commit/3f28580fe0336b0ce517bb43ad437a89ea9f6966))
* move RepositoryWrapper namespace ([95cd4ea](https://github.com/labor-digital/typo3-better-api/commit/95cd4ea8336672a5a646ae99dcf7804aba048840))
* **Translator:** implement v9 bugfixes ([c2a3e23](https://github.com/labor-digital/typo3-better-api/commit/c2a3e233615fc68631f4713e51922fcdd1972b54))
* **TypoScript:** implement v9 bugfixes ([d246902](https://github.com/labor-digital/typo3-better-api/commit/d246902c5135b33a9d8c7ccb209bcca1ed74d8d1))
* add get prefix to link context methods + implement getFileLink ([39f7917](https://github.com/labor-digital/typo3-better-api/commit/39f79179ebb2eebdd6a08c4c022ab35f06c76f34))
* register ContainerAwareTraitPass for di ([8740cc1](https://github.com/labor-digital/typo3-better-api/commit/8740cc1d5ff71f2ddc90fd3ad2f7ce53c8af66a9))
* remove functions.php's from di service resolution ([d5b41e3](https://github.com/labor-digital/typo3-better-api/commit/d5b41e32d9886004ef06b0e3693b7f369e9d6491))

### [10.0.1-beta.0](https://github.com/labor-digital/typo3-better-api/compare/v9.20.0...v10.0.1-beta.0) (2021-05-03)


### Features

* enter "beta" stage ([3b9f6e9](https://github.com/labor-digital/typo3-better-api/commit/3b9f6e9e2a7fb594bbb4e87d9bd8de30f2bb543c))
* make new extension key and namespace compatible with TER ([d77b984](https://github.com/labor-digital/typo3-better-api/commit/d77b984a6876c9937fdb512937fb3a1ece86d0be))
* **AbstractField:** add setDefault() to form fields | from: b45402c1 ([946f135](https://github.com/labor-digital/typo3-better-api/commit/946f135abd706d0a8e043763ce2e89e3f33c6025))
* **BetterLanguageAspect:** getAllFrontendLanguages() can now take a siteIdentifier ([ee625f7](https://github.com/labor-digital/typo3-better-api/commit/ee625f72b4f4c02595a22841ec2764e8de21e4da))
* **BetterLanguageAspect:** implement site switching and getLanguageById() ([1855cbf](https://github.com/labor-digital/typo3-better-api/commit/1855cbff590d82b0d7bdbc89932aa79d377ddedd))
* **Cache:** finalize CacheInterface ([7e1e6a9](https://github.com/labor-digital/typo3-better-api/commit/7e1e6a929459cfa34c5940c337ca5d1706b1a273))
* **CodeGenerationHelperTrait:** implement parseType() for PHP8 compatible type handling ([a3ba96a](https://github.com/labor-digital/typo3-better-api/commit/a3ba96a69aa93980264a7ef52693733440e6eb73))
* **extConfig:** split up ext base content element config ([2391615](https://github.com/labor-digital/typo3-better-api/commit/23916155ea6bc5c776cd47d357466e8ff068eaea))
* **ExtConfig:** implement "raw" handler ([4a0ad56](https://github.com/labor-digital/typo3-better-api/commit/4a0ad564707bd6a7834ccd58e091e5d993c18bc8))
* **ExtConfig:** implement asset registration for frontend ([0ec8479](https://github.com/labor-digital/typo3-better-api/commit/0ec847977120509e569883e369fb752a87dec9a2))
* **ExtConfig:** use the frontend asset registration syntax for the backend, too ([1a89a35](https://github.com/labor-digital/typo3-better-api/commit/1a89a35a9c460529f65b194a21d747332a0eac84))
* **FileRenderer:** implement support for sys_language_uid ([c9099ce](https://github.com/labor-digital/typo3-better-api/commit/c9099cedf3ccc7dbc425cccdf3ddcacc0a8657a2))
* **Log:** implement StreamWriter and BeLogWriter + global logging option ([eeaa38c](https://github.com/labor-digital/typo3-better-api/commit/eeaa38c143e2fc808fa70298a766bf75c0478762))
* **PathFacet:** make getSlugFor() work again ([725c11d](https://github.com/labor-digital/typo3-better-api/commit/725c11dcad394463f0b5a0966fcdc7fadff840ca))
* **ReflectionUtil:** implement getClosestFromStack() method ([6b3977d](https://github.com/labor-digital/typo3-better-api/commit/6b3977d177249394629b491914046fb89560246a))
* **RelationPreset:** add additional options ([733ac1a](https://github.com/labor-digital/typo3-better-api/commit/733ac1a3fe7b42fcc7a9df53148fd9d51e644bb3))
* **Tca:** add removeChildren() for bulk removal ([d898ac0](https://github.com/labor-digital/typo3-better-api/commit/d898ac0f08fb263844e326c8d61538debf5725e4))
* **Translator:** implement translateBe() ([51738a5](https://github.com/labor-digital/typo3-better-api/commit/51738a5cde9c9547bbfd5dd4b5a65d76d8ed9b1e))
* code cleanup and boot time optimization ([36d24ce](https://github.com/labor-digital/typo3-better-api/commit/36d24ce8f84503e48c3a1222654e48d5893cf8f9))
* **ExtConfig:** implement DefaultDependencyInjectionConfig.php to make the setup in extensions easier ([1dbcc77](https://github.com/labor-digital/typo3-better-api/commit/1dbcc77db652f1671d4bf926c165020eb224ef7c))
* **NamingUtil:** implement v9 adjustments ([5b9b2c9](https://github.com/labor-digital/typo3-better-api/commit/5b9b2c9f97d07329119f31a68ad93367ffccdb91))
* finish migrating better query ([850b655](https://github.com/labor-digital/typo3-better-api/commit/850b655d82531413f80cf9aea635a2f5226c48a1))
* finish migrating extended relation resolution ([2191b13](https://github.com/labor-digital/typo3-better-api/commit/2191b139b343f6adae188c6779c55e96c6026bfd))
* finish up most of the migration work and begin cleanup ([592d407](https://github.com/labor-digital/typo3-better-api/commit/592d40750c010367ba2095e2256fcdc85f997713))
* implement content type handling (FEApi - VCols) ([302dc5f](https://github.com/labor-digital/typo3-better-api/commit/302dc5fdc309fae99baf3bec5d63930785176a06))
* implement data hooks as successor of backend actions handler ([690c121](https://github.com/labor-digital/typo3-better-api/commit/690c121d9da23d44a931842e1b6b5994ea84c4ec))
* implement FieldPresetFilterEvent event ([4eff65c](https://github.com/labor-digital/typo3-better-api/commit/4eff65c0b77dc98850062b4d59b83084bb51d099))
* implement page based config + routing config ([1874a59](https://github.com/labor-digital/typo3-better-api/commit/1874a5994d298e83534338a1b76ec2668478cc8b))
* implement remaining TCA migrations and fix all data handler related issues ([14eb2c4](https://github.com/labor-digital/typo3-better-api/commit/14eb2c4d61e347917ac63dd31f7c51b9a098a920))
* implement SingletonInstanceTrait.php ([7409360](https://github.com/labor-digital/typo3-better-api/commit/7409360719366da3e3fc9810850ede486be8f9f1))
* implement tca utility ([3c9dc9e](https://github.com/labor-digital/typo3-better-api/commit/3c9dc9e4b0c3b7af9614de8e2dca09d3ad363f7a))
* implement tsConfig lookup to typoScript service and configFacet ([275adc5](https://github.com/labor-digital/typo3-better-api/commit/275adc5e1f9e66afb642fd70ca74b07d7eb3d7b6))
* inline field preset + plugin flex forms + backend rendering ([7286e2e](https://github.com/labor-digital/typo3-better-api/commit/7286e2ea2bd7baf04f2c2d3dec0a8fd7a4e4a01b))
* make getParent() or getParentContext() methods more speaking ([a0236ca](https://github.com/labor-digital/typo3-better-api/commit/a0236caa7908421e34ea87632783914142f94d07))
* migrate backend preview helpers ([8acf6b5](https://github.com/labor-digital/typo3-better-api/commit/8acf6b5dd08f9e33be04dba07efd7b26648220b8))
* migrate BackendRenderingService ([9c6bb58](https://github.com/labor-digital/typo3-better-api/commit/9c6bb58ae2f6b858110c9f15e029c649d3693b90))
* migrate fluid config option ([34aee06](https://github.com/labor-digital/typo3-better-api/commit/34aee06f3f557ea68aee9b4fb913202f1b770ecb))
* migrate form filter to data hook ([3547260](https://github.com/labor-digital/typo3-better-api/commit/3547260007ca94151d9844e47d5d33c2af38cc79))
* migrate kernel to new singleton interface ([e44b6b2](https://github.com/labor-digital/typo3-better-api/commit/e44b6b2b5b5ace40841be547200fccf06f0eb2fa))
* migrate log configuration ([f2d131f](https://github.com/labor-digital/typo3-better-api/commit/f2d131f30fd6fa855c105a5dc95c2df51cc6827f))
* migrate most of the extbase better query logic ([e8da488](https://github.com/labor-digital/typo3-better-api/commit/e8da4885213907ff32a99db1fae26ff9143fee26))
* migrate most of the flex form logic + implement better table sql builder ([0621f5d](https://github.com/labor-digital/typo3-better-api/commit/0621f5dc01dfde641c657206b179a0e93cf5cbbc))
* migrate most of the tca abstraction to the new version ([3e90752](https://github.com/labor-digital/typo3-better-api/commit/3e90752ef49787cd86e98cb61085c911ea271630))
* migrate multiple link related features + general bugfixes and adjustments ([a475bb5](https://github.com/labor-digital/typo3-better-api/commit/a475bb5b4ca40e67c63a703dbee1d5666c6724d1))
* migrate name space references to T3BA ([5f14c42](https://github.com/labor-digital/typo3-better-api/commit/5f14c427e2303134c1c7c988da74a0f30166db90))
* migrate rendering tools ([39ea0f7](https://github.com/labor-digital/typo3-better-api/commit/39ea0f7d47bd7aedbfe5ee765047e5aed22c2f3d))
* re-implement caching logic ([37bf2ae](https://github.com/labor-digital/typo3-better-api/commit/37bf2ae878a78eec7d05f6309df7855493139a81))
* rework the boot process to fix extension installation issues ([5537b22](https://github.com/labor-digital/typo3-better-api/commit/5537b22bb2057c897fd76943fce2358039544409))
* simplify namespace and class names + varFs optimization for less disk operations ([e531978](https://github.com/labor-digital/typo3-better-api/commit/e5319786ea8d508c3c8639627cd33004654370c7))
* unify getParentContext() methods ([a86b007](https://github.com/labor-digital/typo3-better-api/commit/a86b007b67bff01cf1e393a6a653ce73ccb53299))
* update eventBus package to v3.0.0 ([dedd756](https://github.com/labor-digital/typo3-better-api/commit/dedd756997f5809b8e8261fbd2cec48fab27827a))
* **BetterQuery:** begin migrating BetterQuery ([22366d3](https://github.com/labor-digital/typo3-better-api/commit/22366d39d60c34add14dd11c22c7e9e45679fedd))
* **CommonDependencyTrait:** add DataHandler and Session services ([d78172b](https://github.com/labor-digital/typo3-better-api/commit/d78172bd26005ffc865f8ffd850e90c897cfcab1))
* **CommonDependencyTrait:** add fal service ([787d610](https://github.com/labor-digital/typo3-better-api/commit/787d6100c5b324c952f8c75fb786de3326c00c2b))
* **Database:** migrate database service ([56137c8](https://github.com/labor-digital/typo3-better-api/commit/56137c8d4c2eb0d4bc081190701d8c090c1a8c6e))
* **DataHandler:** implement DataHandlerService and RecordDataHandler ([0746d65](https://github.com/labor-digital/typo3-better-api/commit/0746d6571a7eeb99d3aeace362a02003bbace882))
* **DependencyInjection:** add hasLocalSingleton() to ContainerAwareTraits ([94947e9](https://github.com/labor-digital/typo3-better-api/commit/94947e9a5372172e603f4f680bbd1b38cb3028a2))
* **ExtConfig:** add key definition handling ([b5a4b8a](https://github.com/labor-digital/typo3-better-api/commit/b5a4b8ab77076b0d982d07488e0159e87abfa3e0))
* **ExtConfig:** create group ext config abstract ([9510c0f](https://github.com/labor-digital/typo3-better-api/commit/9510c0fb220f46a63f1fbb2765d05406c0e1b080))
* **ExtConfig:** make the context more general purpose by implementing a shared trait ([5935dcc](https://github.com/labor-digital/typo3-better-api/commit/5935dcc104986eda4dc720fb1ad2a75058baa5de))
* **ExtConfig:** migrate ext base plugin configuration ([85fb4cb](https://github.com/labor-digital/typo3-better-api/commit/85fb4cb0b5367a8902e7d39bc3ba8b72f0969087))
* **FlashMessageRenderingService:** implement better queue detection for extbase controllers ([dd96792](https://github.com/labor-digital/typo3-better-api/commit/dd96792c7d2ddee5f0a04b9c1e2ff6be7cbcacc2))
* **Page:** migrate page service ([de01b3c](https://github.com/labor-digital/typo3-better-api/commit/de01b3c9782a332fa04b71a02c06af42211e1b2f))
* **PageService:** use RecordDataHandler to perform the page actions ([e4c4845](https://github.com/labor-digital/typo3-better-api/commit/e4c48457602d27853ab36178806dc9de68909cd4))
* **PidFacet:** use LocallyCachedStatePropertyTrait to sync pids ([591a6d9](https://github.com/labor-digital/typo3-better-api/commit/591a6d9bfa17da75899d53e4774159f001526857))
* **Simulation:** implement v9 bugfixes + performance improvements ([536d7e1](https://github.com/labor-digital/typo3-better-api/commit/536d7e19f879866393f85ee50415ce5354d78128))
* **TypoContext:** add dependency injection facet ([3fcca89](https://github.com/labor-digital/typo3-better-api/commit/3fcca8999b1f81ed37d18bbbc9c3aa464f54a528))
* **TypoContext:** add getExtensionIconPath() to PathFacet ([4a47bb0](https://github.com/labor-digital/typo3-better-api/commit/4a47bb06ef290632323653ce34d90b45b67e6128))
* **TypoContextAwareTrait:** try to resolve the typo context using the container first ([5fbf4ad](https://github.com/labor-digital/typo3-better-api/commit/5fbf4ad05b29bdb895199bc62c9a50bbd5324517))
* begin to migrate ext-config options ([2c2afff](https://github.com/labor-digital/typo3-better-api/commit/2c2afff52a3dd6da2d35eb954ad70fcba6f54e9a))
* begin to migrate typoScript ext config ([cbaf6e9](https://github.com/labor-digital/typo3-better-api/commit/cbaf6e9bbc37a4cb618050f12c73fbe637aa4607))
* finish implementing kernel and dependency injection configuration ([0623ce0](https://github.com/labor-digital/typo3-better-api/commit/0623ce09e78dde97c96839e1e901afd51130e230))
* implement hook package registration to new kernel ([0e9d903](https://github.com/labor-digital/typo3-better-api/commit/0e9d90316af09f55c93a52ef35a4d6cf36f9ab1a))
* implement new better api kernel ([60db5e8](https://github.com/labor-digital/typo3-better-api/commit/60db5e851742f8d49c06bdcff39e0a7fb5738a19))
* merge generic utilities under Tool\OddsAndEnds ([0665a27](https://github.com/labor-digital/typo3-better-api/commit/0665a272a618aca0a4e5f28f52775b3ae96fe04e))
* migrate ext base controller addons ([bd628f2](https://github.com/labor-digital/typo3-better-api/commit/bd628f2a2582d8783ece4b84422a1236e0c45bc3))
* migrate ext base module config handler ([cc7c861](https://github.com/labor-digital/typo3-better-api/commit/cc7c86184caf59b2f275043b3ffc01fc1d080de1))
* migrate fal service ([2f91d08](https://github.com/labor-digital/typo3-better-api/commit/2f91d08460c5d047864b35c098ecad4fe87cad98))
* migrate Kint debugger TYPO3 bridge ([d8bfdf8](https://github.com/labor-digital/typo3-better-api/commit/d8bfdf836b82bb3a9d357aacb77ee04c12f0fa9c))
* migrate lazy loading util ([75a0ac7](https://github.com/labor-digital/typo3-better-api/commit/75a0ac7a752dc2a85492832d8f73b36397795f5c))
* migrate link service ([159db1f](https://github.com/labor-digital/typo3-better-api/commit/159db1f6328ed21d3ba0737b5b2015a699f1140d))
* migrate more init work into the kernels boot stages ([5bb20f8](https://github.com/labor-digital/typo3-better-api/commit/5bb20f80e0a7c1c87303122965ebc3fbab61138f))
* migrate pid handling + bugfixes on facets and context ([4e8807e](https://github.com/labor-digital/typo3-better-api/commit/4e8807ea5c71e3e5993fd5a902dd09aeac173218))
* migrate request collector middleware ([7f5ef8e](https://github.com/labor-digital/typo3-better-api/commit/7f5ef8ebbe9081c2758d060f9d77b13472510159))
* move event classs into sub-namespaces ([57870ef](https://github.com/labor-digital/typo3-better-api/commit/57870efd8e1be550a1fb7870aaae4d4a5cfe2eb5))
* use GeneralUtility::getContainer() instead of makeInstance(ContainerInterface) ([e224f1e](https://github.com/labor-digital/typo3-better-api/commit/e224f1eef0dd1a3af529f60120c87f9c0e5e8c7f))
* **Session:** migrate session handling ([935c2b8](https://github.com/labor-digital/typo3-better-api/commit/935c2b8de011f3fff8fb686f84c0320e8de5adbb))
* **Simulation:** backup pid config before the simulation ([ab38cc7](https://github.com/labor-digital/typo3-better-api/commit/ab38cc7d749d14e18e9229d948a5fbc017581024))
* **Simulation:** migrate environment simulator ([6a3c712](https://github.com/labor-digital/typo3-better-api/commit/6a3c712f642476ca004666334355a470dfd86fcd))
* **Translation:** migrate translation service ([cf153a7](https://github.com/labor-digital/typo3-better-api/commit/cf153a78042424603932c5674e52040455c42b15))
* **Translator:** use LocallyCachedStatePropertyTrait to sync config ([cbc8925](https://github.com/labor-digital/typo3-better-api/commit/cbc892592e1dbb8abc886f29c4e6fe7f14178cf0))
* **Tsfe:** migrate tsfe service ([3d9e72e](https://github.com/labor-digital/typo3-better-api/commit/3d9e72efdbe96b0e091c8b0797ac0f12e857eb7b))
* **TypoContext:** implement TypoContextAwareTrait + static version ([2ab25e3](https://github.com/labor-digital/typo3-better-api/commit/2ab25e39d89ce26cd44d2a2ca743ab078624abf0))
* migrate TypoContext an additional TypoScript settings ([11f350e](https://github.com/labor-digital/typo3-better-api/commit/11f350e667aa05499ca08db3cd61c5c36bff9ac0))
* move ExtConfig related stuff into its own module ([541a1db](https://github.com/labor-digital/typo3-better-api/commit/541a1db5c2921280a73dca97fd5bd5ad5d1817d5))
* scrap the "module" approach and build a "sane" classes architecture instead ([0181a5c](https://github.com/labor-digital/typo3-better-api/commit/0181a5cfca61a1ca821060d889ea584c829eea46))


### Bug Fixes

* remove no longer required whitecube/lingua package ([dfc448f](https://github.com/labor-digital/typo3-better-api/commit/dfc448f521a07c8c67906e3cf7973de54ea71482))
* **AbstractActionEvent:** handle "pasteSpecialData" as "mixed" data type ([d634773](https://github.com/labor-digital/typo3-better-api/commit/d6347735800667ab154aee070c395e7d5d99aa36))
* **CacheConfiguratorPass:** prefer using CodeGenerationHelper for type parsing ([ef05c4c](https://github.com/labor-digital/typo3-better-api/commit/ef05c4ccb770c5ac21b0ad75e02003cec001837b))
* **CodeGenerationHelperTrait:** make sure return type is generated correctly ([55222ba](https://github.com/labor-digital/typo3-better-api/commit/55222ba527c442e2eb588893d62665935631693e))
* **ConfigArrayPostProcEventAdapter:** actually modify the parameter array ([7aa7eec](https://github.com/labor-digital/typo3-better-api/commit/7aa7eec52b2dc56285fee3f0675b6addcdcccdc6))
* **ConfigureFrontendInterface:** make configureFrontend() site context aware ([a03b8b7](https://github.com/labor-digital/typo3-better-api/commit/a03b8b705b1114f34a170af9a8e0304e4c4a59d4))
* **ContentType:** don't reexecute loader in cli or install tool ([fdfce71](https://github.com/labor-digital/typo3-better-api/commit/fdfce717c677928db28b73e42c2a84accd3fdde0))
* **Custom\Field:** finish implementing CustomFieldDataHookContext ([1a855a0](https://github.com/labor-digital/typo3-better-api/commit/1a855a09c1043c44451c7386250e5d56a5b2132e))
* **Custom\Field:** fix context type annotation for applyCustomElementPreset() ([7b5830c](https://github.com/labor-digital/typo3-better-api/commit/7b5830c9e7821a41320b59b7ce0ffae9c4a767fd))
* **Custom\Field:** fix data hook option generation in CustomFieldPresetTrait ([f3fc307](https://github.com/labor-digital/typo3-better-api/commit/f3fc307c480ff885443bec6440283a56cf662263))
* **DataHook:** finish implementing FlexFormFieldPacker ([4175408](https://github.com/labor-digital/typo3-better-api/commit/4175408c903556a219ce4ee16521029dc92f6910))
* **DbService:** use NamingUtil::resolveTableName in getQueryBuilder() ([f8ac6e6](https://github.com/labor-digital/typo3-better-api/commit/f8ac6e64e7ea4a3aba86978657e77c2e67bf2c6b))
* **DelegateContainer:** set return time of get() to "mixed" ([1c8544f](https://github.com/labor-digital/typo3-better-api/commit/1c8544ff4c5c51ce7c49f9e79f4806bb13177da3))
* **DisplayConditionTrait:** make array based condition handling more reliable ([d5aaa4e](https://github.com/labor-digital/typo3-better-api/commit/d5aaa4e3c6b962378bc64de618055f2371a340d6)), closes [#43b0c52](https://github.com/labor-digital/typo3-better-api/issues/43b0c52)
* **ExtConfig:** correctly apply log configuration ([8951d2b](https://github.com/labor-digital/typo3-better-api/commit/8951d2b2029815a68aa7c6c645265d9662d563c8))
* **ExtConfig:** rename ConfigureTypoScriptInterface::configure to configureTypoScript ([4513f50](https://github.com/labor-digital/typo3-better-api/commit/4513f50dd28b9e9d2028172d69971f4ebb62f344))
* **ExtConfig\SiteBased:** make sure site based config does not get overwritten ([c3adc67](https://github.com/labor-digital/typo3-better-api/commit/c3adc6755f1d6e2c12a0347a04fe372d5ddd17c1))
* **ExtConfig\Table:** decouple table name generation from extKey / namespace ([561ce28](https://github.com/labor-digital/typo3-better-api/commit/561ce284009526c89ba640b242a4e55c89e9d1a7))
* **ExtConfig\TypoScript:** rename ConfigureTypoScriptInterface::configure to configureTypoScript ([16288ad](https://github.com/labor-digital/typo3-better-api/commit/16288ad49d4b285adb861f6b0bd4ac19b914e0e6))
* **ExtendedSiteConfiguration:** don't pollute the site config yml files when saving the min the backend ([b0c568b](https://github.com/labor-digital/typo3-better-api/commit/b0c568be9e2817b2f997653be4931032e7ee1c48))
* **Fal:** generate file urls correctly even with external FAL drivers | from: c60f03c1 ([df4c571](https://github.com/labor-digital/typo3-better-api/commit/df4c571c9438d163c1092c1a91947942b43ab369))
* **Link:** finish implementing cacheHash disabling on links ([94f0655](https://github.com/labor-digital/typo3-better-api/commit/94f065506a0652c0cb16ca5e77417b0e079bbd3d))
* **Link:** remove no longer required "else" when no language is selected ([5225508](https://github.com/labor-digital/typo3-better-api/commit/52255082e22a22004d2de95f21aec6eaaf15b137))
* **LinkService:** handle "fragment:" prefix in $args of getLink() ([c19c179](https://github.com/labor-digital/typo3-better-api/commit/c19c17999642c4ec5aae3d937f31ae40c5742a8d))
* **RoutingConfigurator:** remove unused $site property ([f04eb16](https://github.com/labor-digital/typo3-better-api/commit/f04eb169a8a3ed65ec7d6fed96555b2a68209407))
* **SiteFacet:** make sure "NullSites" are ignored correctly ([3c2781f](https://github.com/labor-digital/typo3-better-api/commit/3c2781fac99d676276221341f36ab93b5f5f608a))
* **StandaloneBetterQuery:** handle empty results in getRelatedRecords correctly ([04cdd15](https://github.com/labor-digital/typo3-better-api/commit/04cdd1508d3342031ef00c79131e94963450fcc2))
* **TypoScriptService:** finish implementing renderContentObject ([2800106](https://github.com/labor-digital/typo3-better-api/commit/2800106a06fea443b866d71b12777123e07eb80a))
* **TypoScriptService:** fix minor bug in TypoScriptConfigurationManager ([9a3a39b](https://github.com/labor-digital/typo3-better-api/commit/9a3a39bf89dbcba5fcb71832a660a6cd2964599d))
* **TypoScriptService:** load tsConfig from TSFE if possible ([a5bdb7c](https://github.com/labor-digital/typo3-better-api/commit/a5bdb7cc01656efc5cdd05d0663a51faa614dd02))
* **VarFs:** only remove the temp fs cache data if the "all" cache is cleared without any tags | from: 8daf4de9 ([96a490e](https://github.com/labor-digital/typo3-better-api/commit/96a490e0bfdc66de953202294693bcecd0527928))
* fix code a multitude of minor issues and weak warnings ([f71f101](https://github.com/labor-digital/typo3-better-api/commit/f71f10135806c8997dfd733660c34209ad6a6ee8))
* harden ext config di loading process ([8059c3d](https://github.com/labor-digital/typo3-better-api/commit/8059c3dace7b27298a479747b8f7abf5bf5bb7b9))
* **BackendPreview:** extend image file rendering ([f8cdc86](https://github.com/labor-digital/typo3-better-api/commit/f8cdc868fec82c9b504143c64ae236a7b3094182))
* **BackendPreview:** make config generation more reliable ([7170967](https://github.com/labor-digital/typo3-better-api/commit/7170967359111d4fc55b399d0cae1ffddd70e4b5))
* **BetterQuery:** allow whereUidSpecialConstraintWrapper to return strings ([831db88](https://github.com/labor-digital/typo3-better-api/commit/831db88813a979ce00f9fbb753ecdeff10e5427d))
* **BootStage:** make sure ConfigState is available at all times ([4d0efd3](https://github.com/labor-digital/typo3-better-api/commit/4d0efd3fea5156314188c10a35da975b4ff3b2fe))
* **CacheClearedEvent:** make sure to emit event on all required methods ([44f8647](https://github.com/labor-digital/typo3-better-api/commit/44f8647769eaa278602a799b3b371ce97317a6dd))
* **CommonDependencyTrait:** make Session() and DataHandler() protected ([6ed65f7](https://github.com/labor-digital/typo3-better-api/commit/6ed65f7281ef5d9e78afafd68939ee422688367e))
* **ContainerAwareTrait:** rename injectContainer() to setContainer() to fix extBase issues ([e8dcc47](https://github.com/labor-digital/typo3-better-api/commit/e8dcc470abab278e5637a1f6ab03bfe3ad610c3e))
* **ContentType:** harden ContentTypeUtil against array 'cType' entries ([4cd2463](https://github.com/labor-digital/typo3-better-api/commit/4cd2463fac3b60b5c5dacf6a5406da690f5b7185))
* **DataHandler:** auto-login when used in cli ([d22d8b3](https://github.com/labor-digital/typo3-better-api/commit/d22d8b3c8a40e9617836eb9be1427405d47c5a7d))
* **DelegateContainer:** fix container issue in install tool ([1b2765a](https://github.com/labor-digital/typo3-better-api/commit/1b2765a3410ed7ead9ebfe94a6c88d0cfe1233d2))
* **DependencyInjection:** remove unnecessary singleton update in getInstanceOf() ([0adbda3](https://github.com/labor-digital/typo3-better-api/commit/0adbda351663b92927175b5dcf67a13ba4be0931))
* **EventBus:** simplify class implementation ([0a52ce9](https://github.com/labor-digital/typo3-better-api/commit/0a52ce96a3c94ba51ad8fa7e48e19bbb5ecf8e27))
* **ExtBase:** finish migrating action controller ([c71e319](https://github.com/labor-digital/typo3-better-api/commit/c71e31941594537a330271585861c454cb0f8a7e))
* **ExtConfig:** fix bugs in plugin config generation ([9ece726](https://github.com/labor-digital/typo3-better-api/commit/9ece726733e9336fe528c4e1994df41617eb9930))
* **ExtConfig:** mark handler abstracts as public services ([e6adc92](https://github.com/labor-digital/typo3-better-api/commit/e6adc92b7d2d612198a2970a86d5e3cfcafe2bc5))
* **ExtConfigEventHandler:** remove dev fractal ([d64600c](https://github.com/labor-digital/typo3-better-api/commit/d64600cc1df6d616a70e60b2e64a23a657e940f2))
* **FalService:** fix type error when using integer as uid ([10e917d](https://github.com/labor-digital/typo3-better-api/commit/10e917dd030d6efd7bb7bac062b8de543aee5107))
* **Fluid:** fix incorrect view helper configuration ([18e533e](https://github.com/labor-digital/typo3-better-api/commit/18e533e6766d542db24aa9b16f56e908d159cff7))
* **Link:** add missing allowLinkSets option to field preset ([f6ad8bc](https://github.com/labor-digital/typo3-better-api/commit/f6ad8bc94268f5d63fbdc74903ac327506e0578f))
* **LinkContext:** create the extbase request as local singleton ([a16d3a1](https://github.com/labor-digital/typo3-better-api/commit/a16d3a16977bf38d9861caa74b32a6c40988228d))
* **PageService:** implement v9 bugfix for root line generation ([92e08e3](https://github.com/labor-digital/typo3-better-api/commit/92e08e30bf80faa4ec03ac48909a832806672955))
* **RecordDataHandler:** use correct "undelete" command ([33134d1](https://github.com/labor-digital/typo3-better-api/commit/33134d1190e4446c714e7317e5edf35fdd1d0d02))
* **Simulation:** mark simulator passes as public ([58c0ec1](https://github.com/labor-digital/typo3-better-api/commit/58c0ec1cb6f7663f1bbd5c8d8f22f4fd93b4da26))
* **Tca:** avoid dumping showitem without sensible content ([56169c0](https://github.com/labor-digital/typo3-better-api/commit/56169c0421f6469038707831634385e3a312f371))
* **Tca:** getSortedNodes() now returns children in palettes ([fb0997d](https://github.com/labor-digital/typo3-better-api/commit/fb0997daa13460e0a67254ec2c4af3da71bdf03b))
* ExtendedNodeFactory now extends the correct parent ([3f28580](https://github.com/labor-digital/typo3-better-api/commit/3f28580fe0336b0ce517bb43ad437a89ea9f6966))
* move RepositoryWrapper namespace ([95cd4ea](https://github.com/labor-digital/typo3-better-api/commit/95cd4ea8336672a5a646ae99dcf7804aba048840))
* **Translator:** implement v9 bugfixes ([c2a3e23](https://github.com/labor-digital/typo3-better-api/commit/c2a3e233615fc68631f4713e51922fcdd1972b54))
* **TypoScript:** implement v9 bugfixes ([d246902](https://github.com/labor-digital/typo3-better-api/commit/d246902c5135b33a9d8c7ccb209bcca1ed74d8d1))
* add get prefix to link context methods + implement getFileLink ([39f7917](https://github.com/labor-digital/typo3-better-api/commit/39f79179ebb2eebdd6a08c4c022ab35f06c76f34))
* register ContainerAwareTraitPass for di ([8740cc1](https://github.com/labor-digital/typo3-better-api/commit/8740cc1d5ff71f2ddc90fd3ad2f7ce53c8af66a9))
* remove functions.php's from di service resolution ([d5b41e3](https://github.com/labor-digital/typo3-better-api/commit/d5b41e32d9886004ef06b0e3693b7f369e9d6491))



### Features

* bump to stable v10 ([16bf5fa](https://github.com/labor-digital/typo3-better-api/commit/16bf5fa9a03fa087ee8684aa75bcb9e481847b25))

### [9.29.4](https://github.com/labor-digital/typo3-better-api/compare/v9.29.3...v9.29.4) (2021-04-13)


### Bug Fixes

* **BeLogWriter:** ensure that %s gets not removed in be output ([2a216c7](https://github.com/labor-digital/typo3-better-api/commit/2a216c7eb08fa5b6a24babee7c2c59a7cf7de591))

### [9.29.3](https://github.com/labor-digital/typo3-better-api/compare/v9.29.2...v9.29.3) (2021-04-13)


### Bug Fixes

* **BeLogWriter:** don't add data in the "log_data" field ([ee38e04](https://github.com/labor-digital/typo3-better-api/commit/ee38e04f86f68d5c5e6b5f64454841c8668e7fe9))
* **BeLogWriter:** remove prepending dash in "data" field ([c234cb2](https://github.com/labor-digital/typo3-better-api/commit/c234cb239878a93d115c59253b6935315d9ed7c6))

### [9.29.2](https://github.com/labor-digital/typo3-better-api/compare/v9.29.1...v9.29.2) (2021-03-18)


### Bug Fixes

* **Log:** make sure log configuration gets merged correctly ([044d9e4](https://github.com/labor-digital/typo3-better-api/commit/044d9e42cde71fbfd8f4db0b330798cc4e0d1b79))

### [9.29.1](https://github.com/labor-digital/typo3-better-api/compare/v9.29.0...v9.29.1) (2021-03-18)

## [9.29.0](https://github.com/labor-digital/typo3-better-api/compare/v9.28.2...v9.29.0) (2021-03-18)


### Features

* **Log:** implement BeLogWriter ([b436706](https://github.com/labor-digital/typo3-better-api/commit/b4367066e20a61d27d5304740f901cfc9084822e))
* **Log:** implement StreamWriter + global logging option ([f437237](https://github.com/labor-digital/typo3-better-api/commit/f437237985e40ad2503cc3e4ed5f1de41ccb3e56))

### [9.28.2](https://github.com/labor-digital/typo3-better-api/compare/v9.28.1...v9.28.2) (2021-02-18)


### Bug Fixes

* **LinkSetDefinition:** fix documentation issue ([de4b903](https://github.com/labor-digital/typo3-better-api/commit/de4b903cc2d524845446481c587b0c97e0a9dbbe))

### [9.28.1](https://github.com/labor-digital/typo3-better-api/compare/v9.28.0...v9.28.1) (2021-02-03)


### Bug Fixes

* **TypoScriptService:** load pageTsConfig at the correct event ([976791f](https://github.com/labor-digital/typo3-better-api/commit/976791fd7fbbc1afa6edfc26e81153f92655d6c0))

## [9.28.0](https://github.com/labor-digital/typo3-better-api/compare/v9.27.0...v9.28.0) (2021-01-21)


### Features

* **EnvFacet:** add isFeDebug() and isFeDebug() helpers ([0d13f57](https://github.com/labor-digital/typo3-better-api/commit/0d13f570d7200f46fecf3cb08e13501964cbd2cf))

## [9.27.0](https://github.com/labor-digital/typo3-better-api/compare/v9.26.6...v9.27.0) (2020-12-07)


### Features

* **RelationPreset:** add additional options ([a0f807d](https://github.com/labor-digital/typo3-better-api/commit/a0f807d09a1c50243aa6510cd45907fe12250ffb))

### [9.26.6](https://github.com/labor-digital/typo3-better-api/compare/v9.26.5...v9.26.6) (2020-12-02)


### Bug Fixes

* **LinkSetRecordLinkBuilder:** handle failing link generation correctly ([3e6328c](https://github.com/labor-digital/typo3-better-api/commit/3e6328c98a1b65994a78dc930744abecbc8221fa))

### [9.26.5](https://github.com/labor-digital/typo3-better-api/compare/v9.26.4...v9.26.5) (2020-11-27)


### Bug Fixes

* **StandaloneBetterQuery:** handle empty results in getRelatedRecords correctly ([7a1ae35](https://github.com/labor-digital/typo3-better-api/commit/7a1ae35218f98c3498a917be50d0efd34c36fa67))

### [9.26.4](https://github.com/labor-digital/typo3-better-api/compare/v9.26.3...v9.26.4) (2020-11-02)


### Bug Fixes

* **DisplayConditionTrait:** make array based condition handling more reliable ([43b0c52](https://github.com/labor-digital/typo3-better-api/commit/43b0c52da521460763854cd675f8040efd65c9e7))

### [9.26.3](https://github.com/labor-digital/typo3-better-api/compare/v9.26.2...v9.26.3) (2020-10-30)


### Bug Fixes

* **SharedCustomElementTrait:** access the correct "customElementOptions" field to read the dataFunc definition ([36c0c9c](https://github.com/labor-digital/typo3-better-api/commit/36c0c9c6dd9f38a2b96d88a9ba4dd195dadad8be))

### [9.26.2](https://github.com/labor-digital/typo3-better-api/compare/v9.26.1...v9.26.2) (2020-10-29)


### Bug Fixes

* **BackendPreviewRenderer:** add a linebreak to the beginning of the preview ([c244512](https://github.com/labor-digital/typo3-better-api/commit/c244512e331fc962a6f0af6f6a732f4a0408b62a))

### [9.26.1](https://github.com/labor-digital/typo3-better-api/compare/v9.26.0...v9.26.1) (2020-10-29)


### Bug Fixes

* **Simulator:** update tsfe globals entry even if using a cached tsfe instance ([74f0f40](https://github.com/labor-digital/typo3-better-api/commit/74f0f400af62034b24c76847d006068e805aa75e))

## [9.26.0](https://github.com/labor-digital/typo3-better-api/compare/v9.25.2...v9.26.0) (2020-10-29)


### Features

* **TypoScript:** backport the v10 dynamic typoScript injection method for v9 ([23c9b2f](https://github.com/labor-digital/typo3-better-api/commit/23c9b2fa6ed5286f8f4035908396277eb87f4f7e))

### [9.25.2](https://github.com/labor-digital/typo3-better-api/compare/v9.25.1...v9.25.2) (2020-10-29)


### Bug Fixes

* **Simulation:** multiple performance improvements ([ae40483](https://github.com/labor-digital/typo3-better-api/commit/ae40483bd8f2b3d7654c8aa736955f2808c93cc1))

### [9.25.1](https://github.com/labor-digital/typo3-better-api/compare/v9.25.0...v9.25.1) (2020-10-15)


### Bug Fixes

* **LinkSetRecordLinkBuilder:** only build external links if required ([6e06cef](https://github.com/labor-digital/typo3-better-api/commit/6e06cefbc942ad52519b0ada9e5fee036ff65a2c))
* write cache key in lower case to prevent db issues ([b4d4e9e](https://github.com/labor-digital/typo3-better-api/commit/b4d4e9e4a4b9c3a712a17aab88faab7c99af7000))

## [9.25.0](https://github.com/labor-digital/typo3-better-api/compare/v9.24.2...v9.25.0) (2020-10-14)


### Features

* **DbService:** add support to use extbase model name as table name in getQuery() ([259cdbc](https://github.com/labor-digital/typo3-better-api/commit/259cdbc3a215f4e948ab8d5f719c4f46b654d6fb))
* **Link:** allow link browser registration if a required fragment is present ([560d744](https://github.com/labor-digital/typo3-better-api/commit/560d7444be10f5d9c5cf73a4b5602760d95d7138))
* **Naming:** implement resolveTableName and resolveCallable methods ([1dbefc3](https://github.com/labor-digital/typo3-better-api/commit/1dbefc3d3f2105b37e014fd01b7fe312e1d8395a))
* **TypoLink:** add support for complex pid types. ([f0016fc](https://github.com/labor-digital/typo3-better-api/commit/f0016fc370b568abdda04ad02b06ebe77f47a1c9))


### Bug Fixes

* **LinkSetGenerator:** add missing, closing brace ([9df3468](https://github.com/labor-digital/typo3-better-api/commit/9df34683f49251177737b99a803cb9e68a7c6f78))

### [9.24.2](https://github.com/labor-digital/typo3-better-api/compare/v9.24.1...v9.24.2) (2020-10-09)


### Bug Fixes

* **SiteFacet:** make sure "NullSites" are ignored correctly ([3b0c483](https://github.com/labor-digital/typo3-better-api/commit/3b0c483dc371223ff8624f04aaa51217b787d1ac))

### [9.24.1](https://github.com/labor-digital/typo3-better-api/compare/v9.24.0...v9.24.1) (2020-10-09)


### Bug Fixes

* **BetterQuery:** rework language handling ([162fcfa](https://github.com/labor-digital/typo3-better-api/commit/162fcfa505ddb14fb898e3455f9630ef9caceae3))

## [9.24.0](https://github.com/labor-digital/typo3-better-api/compare/v9.23.0...v9.24.0) (2020-10-08)


### Features

* **BetterQuery:** allow advanced options on withLanguage() method ([89b5f80](https://github.com/labor-digital/typo3-better-api/commit/89b5f8018794a457ca72d307450898a316babc87))
* **StandaloneBetterQuery:** add $forSelect property to getQueryBuilder() method ([3bb94e2](https://github.com/labor-digital/typo3-better-api/commit/3bb94e27787e9b95ace6f35126a07cfb87d50b38))


### Bug Fixes

* **StandaloneBetterQuery:** remove dev fragment ([83dd3be](https://github.com/labor-digital/typo3-better-api/commit/83dd3bea4f6ebc6cdef2740d5a8ce9c60148f144))

## [9.23.0](https://github.com/labor-digital/typo3-better-api/compare/v9.22.0...v9.23.0) (2020-10-06)


### Features

* **TranslationService:** implement translateBe() + deprecate translateMaybe() ([793ca38](https://github.com/labor-digital/typo3-better-api/commit/793ca38d404e7c7103783d463b92e42ea07a99e9))
* implements option to use "linkSets" as link handlers in the link browser using feature: [#79626](https://github.com/labor-digital/typo3-better-api/issues/79626) ([8c2c6ae](https://github.com/labor-digital/typo3-better-api/commit/8c2c6ae4c46be56a7c405440594567313b9197b2))
* **ConfigFacet:** add getTsConfigValue() to retrieve tsConfig options ([bf61468](https://github.com/labor-digital/typo3-better-api/commit/bf61468287976b2fb50ed821517451d7816cfda3))
* **CoreConfigOption:** implement registerRawConfig() to add global configuration through ExtConfig ([8995d12](https://github.com/labor-digital/typo3-better-api/commit/8995d12d0a128393bc4c5fb315400ed5e3b850be))
* **Event:** implement LinkBrowserAllowedTabsFilterEvent to filter the link browser tabs ([1f5daaf](https://github.com/labor-digital/typo3-better-api/commit/1f5daaf1d9e74f2deeabec5705fb20ef1b7b64a5))


### Bug Fixes

* **BackendPreviewService:** render element labels in the backend and not the frontend language ([e069b06](https://github.com/labor-digital/typo3-better-api/commit/e069b065d6399cf68dc6a008ed62c02024e4cdbd))
* **ExtendedSiteConfiguration:** don't pollute the site config yml files when saving the min the backend ([886e23b](https://github.com/labor-digital/typo3-better-api/commit/886e23b6bb4e4c4e91f4968442afb35627031543))
* **LinkSetLinks:** add the missing link generator parts ([7a45d5f](https://github.com/labor-digital/typo3-better-api/commit/7a45d5fb45c5dea86dc64713569d14b6be393a45))
* **PageService:** generate root line more reliably ([f5f630d](https://github.com/labor-digital/typo3-better-api/commit/f5f630d7085a7acfa5466157a6f05a5f6879fe72))
* **TypoLink:** add exception if getUriBuilder() could not find an instance ([7a600fe](https://github.com/labor-digital/typo3-better-api/commit/7a600fe39bb4746f461b7ba0a67ea0cabe556917))

## [9.22.0](https://github.com/labor-digital/typo3-better-api/compare/v9.21.1...v9.22.0) (2020-09-28)


### Features

* **Naming:** add tableNameFromModelClass() ([6f0901b](https://github.com/labor-digital/typo3-better-api/commit/6f0901b215514721c3cfa9886e64a6e98b7d80cf))
* **PidFacet:** add getSubSet() to retrieve a pid sub-list ([f4f25a4](https://github.com/labor-digital/typo3-better-api/commit/f4f25a4bd2d031e4a345ca05e7325014e8a456ac))


### Bug Fixes

* **CacheClearedEvent:** use array for $tags instead of a single tag ([f6aa7fa](https://github.com/labor-digital/typo3-better-api/commit/f6aa7faf3139e15ff41a879e8a5bb6e5f330a559))
* **ExtendedCacheManager:** add missing flushCachesInGroupByTags() and flushCachesByTags() overrides ([80c4075](https://github.com/labor-digital/typo3-better-api/commit/80c40755a50c4f2c58b99f017ca7e453b64140d0))
* **TempFs:** only remove the temp fs cache data if the "all" cache is cleared without any tags ([8daf4de](https://github.com/labor-digital/typo3-better-api/commit/8daf4de9e9d83e4387d4798310f4395fc1c86c71))

### [9.21.1](https://github.com/labor-digital/typo3-better-api/compare/v9.21.0...v9.21.1) (2020-09-10)


### Bug Fixes

* **BetterApiInit:** don't kill TYPO if no extension uses BetterApi ([3038f96](https://github.com/labor-digital/typo3-better-api/commit/3038f96995917e6aa294af4d799fc974e7fab058))

## [9.21.0](https://github.com/labor-digital/typo3-better-api/compare/v9.20.0...v9.21.0) (2020-08-27)


### Features

* add setDefault() to form fields ([b45402c](https://github.com/labor-digital/typo3-better-api/commit/b45402c15ea8bf6d914d3e9d1b57739624f024e5))


### Bug Fixes

* **FalFileService:** generate file urls correctly even with external FAL drivers ([c60f03c](https://github.com/labor-digital/typo3-better-api/commit/c60f03c13a8a33c72b7ddf19303120b77ce98e8b))

## [9.20.0](https://github.com/labor-digital/typo3-better-api/compare/v9.19.1...v9.20.0) (2020-08-18)


### Features

* **BackendForms:** better handling for basePid and mapping for group tca fields ([195a6f7](https://github.com/labor-digital/typo3-better-api/commit/195a6f79bb41acb12efff8ddd2fce4dd8c37394a))
* **StandaloneBetterQuery:** add option to resolve domain model from relatedRecordRow ([5eb5b8d](https://github.com/labor-digital/typo3-better-api/commit/5eb5b8d67a05186982c0263934e58eb1341c0f94))


### Bug Fixes

* **StandaloneBetterQuery:** fix invalid sql generation in getRelated() ([2ce89d9](https://github.com/labor-digital/typo3-better-api/commit/2ce89d993200cb688e4d4cc20b96d03f9508d0da))
* **StandaloneBetterQuery:** require a array field list instead of a single field for getRelated ([f479911](https://github.com/labor-digital/typo3-better-api/commit/f479911425f7891b34c98fdd34e14a169db5b9e7))

### [9.19.1](https://github.com/labor-digital/typo3-better-api/compare/v9.19.0...v9.19.1) (2020-08-14)

## [9.19.0](https://github.com/labor-digital/typo3-better-api/compare/v9.18.1...v9.19.0) (2020-08-14)


### Features

* **Simulation:** use SimulatedTypoScriptFrontendController instead of the normal TypoScriptFrontendController to determine if the frontend was simulated or not ([be4f557](https://github.com/labor-digital/typo3-better-api/commit/be4f557fea15757691c6ab72a5879bfa58c10d49))


### Bug Fixes

* **CodeGenerationHelperTrait:** fix deprecated function call ([e674593](https://github.com/labor-digital/typo3-better-api/commit/e67459334adc0c7f92cf172a7cd1727878e9a84b))

### [9.18.1](https://github.com/labor-digital/typo3-better-api/compare/v9.18.0...v9.18.1) (2020-08-07)


### Bug Fixes

* **BackendListLabelFilterEvent:** make row settable ([9e9cf2d](https://github.com/labor-digital/typo3-better-api/commit/9e9cf2d27a2485713d537b2682168dffd7bcd1c8))
* **BackendPreview:** minor code style adjustments ([3d6c61e](https://github.com/labor-digital/typo3-better-api/commit/3d6c61eb56e0758a006b7cf068ee0a1019d7b46c))

## [9.18.0](https://github.com/labor-digital/typo3-better-api/compare/v9.17.1...v9.18.0) (2020-08-06)


### Features

* deprecate the Translation\Filesync namespace. ([cae55b4](https://github.com/labor-digital/typo3-better-api/commit/cae55b4697321af0a66ea14fb01b87455195c2bf))

### [9.17.1](https://github.com/labor-digital/typo3-better-api/compare/v9.17.0...v9.17.1) (2020-07-21)


### Bug Fixes

* **Simulation:** allow true as a fallback language and add correct documentation for it ([849c99a](https://github.com/labor-digital/typo3-better-api/commit/849c99a602d0d4523697aebdb1864e6957469103))

## [9.17.0](https://github.com/labor-digital/typo3-better-api/compare/v9.16.2...v9.17.0) (2020-07-21)


### Features

* **BetterQuery:** Implement runInTransaction() to run a command stack inside a DB transaction ([12c97a4](https://github.com/labor-digital/typo3-better-api/commit/12c97a4782e5b6e8fb13e86ab3b506989b53a228))
* **Container:** Deprecate LazyServiceDependencyTrait and replace CommonServiceDependencyTrait with CommonDependencyTrait. Also adds getSingletonOf() method to the ContainerAwareTrait ([a6c3727](https://github.com/labor-digital/typo3-better-api/commit/a6c3727ab12f05ade7f4776e75f3abee8bb77f70))
* **Simulation:** complete rewrite of the environment simulator to clean up the code ([5c1014d](https://github.com/labor-digital/typo3-better-api/commit/5c1014d33d7a23dfaa681800cff40cd98e2594d5))
* **SiteFacet:** rewrite siteFacet ([dab2b8a](https://github.com/labor-digital/typo3-better-api/commit/dab2b8aef805b4cdac7f2dda81b602cabc8174ef))


### Bug Fixes

* **checkbox:** add correct wrapper to checkbox -> inverted items ([24146bd](https://github.com/labor-digital/typo3-better-api/commit/24146bd15e564a73f6dc8dd950cfa836959ed167))
* **Container:** Deprecate LazyServiceDependencyTrait ([aeeb087](https://github.com/labor-digital/typo3-better-api/commit/aeeb08719151ac185f99648a88b67a37b3a01b86))
* **ContainerAwareTrait:** remove self annotation for setLocalSingleton() ([3c95628](https://github.com/labor-digital/typo3-better-api/commit/3c956281fa3796c8c7ef6e4c242132460e43cc03))
* **ExtConfig:** make sure non-instantiable classes are ignored when classes in a directory are gathered ([7dc1ba9](https://github.com/labor-digital/typo3-better-api/commit/7dc1ba9e999073b7f340833fd81af42425b8b7f4))
* fix doc dependencies ([02d3e90](https://github.com/labor-digital/typo3-better-api/commit/02d3e90c6a950b896dacd5748ec6ea9f47b7ab7d))
* **PidFacet:** more reliably handle the pid resolution ([c99c723](https://github.com/labor-digital/typo3-better-api/commit/c99c723dcf69c6cb90a1f5ee91467510c15efc8a))

### [9.16.2](https://github.com/labor-digital/typo3-better-api/compare/v9.16.1...v9.16.2) (2020-07-09)


### Bug Fixes

* **PageService:** add $includeAllNotDeleted to getPageInfo() ([38c49e9](https://github.com/labor-digital/typo3-better-api/commit/38c49e9647dac4e52045c5158186a7aae08585ed))
* **StandaloneBetterQuery:** fix issues with virtual columns when using "getRelations" ([8b5cdbc](https://github.com/labor-digital/typo3-better-api/commit/8b5cdbc6e6f9735872ea1cbb8429bafa4d6fae34))

### [9.16.1](https://github.com/labor-digital/typo3-better-api/compare/v9.16.0...v9.16.1) (2020-07-01)


### Bug Fixes

* **TranslationFileSync:** make sure to use our recommended XML format ([e3ef292](https://github.com/labor-digital/typo3-better-api/commit/e3ef29284b05068872fee997a27828987decfa8b))

## [9.16.0](https://github.com/labor-digital/typo3-better-api/compare/v9.15.1...v9.16.0) (2020-07-01)


### Features

* **CommonServiceDependencyTrait:** add session providers for lookup ([686da8d](https://github.com/labor-digital/typo3-better-api/commit/686da8d52fc2fca6f8a5b38ed63f89d026278636))
* **RteConfig:** make sure the RTE config can be given without restructuring ([45a327e](https://github.com/labor-digital/typo3-better-api/commit/45a327ef9f7971fcc5a396db4a6166c132c95ac5))
* **StandaloneBetterQuery:** allow select field definition on "getAll" and "getFirst" ([7fbf3c3](https://github.com/labor-digital/typo3-better-api/commit/7fbf3c31b0348727b73be5fb987bfd278d4f4a41))


### Bug Fixes

* **CustomElements:** make handler compatible with new dataHandlerActionHandler naming ([739e0f0](https://github.com/labor-digital/typo3-better-api/commit/739e0f06d3429735dfd2f0c65701fed0d789f7f8))
* **DataHandlerActionHandler:** fix addContextsForStack() arguments to prevent wrong config in action context ([97e1137](https://github.com/labor-digital/typo3-better-api/commit/97e1137ea0abaf6d85523a8738aed387bb1d8931))
* **PageService:** make sure pageExists() returns the correct value if the doktype is bigger than 200 ([0f81d91](https://github.com/labor-digital/typo3-better-api/commit/0f81d919be2d0b9553a286822176c8be872033f8))
* **PidFacet:** return correct page id when reading it from the returnUrl ([3df94af](https://github.com/labor-digital/typo3-better-api/commit/3df94af9875dce937466877015be4e42eb682178))

### [9.15.1](https://github.com/labor-digital/typo3-better-api/compare/v9.15.0...v9.15.1) (2020-06-25)


### Bug Fixes

* **TranslationFileSync:** make sure to sort the translation messages after a sync ([709fcb9](https://github.com/labor-digital/typo3-better-api/commit/709fcb979452ec4baefd65bb216065f31b85b05f))

## [9.15.0](https://github.com/labor-digital/typo3-better-api/compare/v9.14.0...v9.15.0) (2020-06-22)


### Features

* **Http:** add support for storage pid aware persisted alias mapping ([69475dc](https://github.com/labor-digital/typo3-better-api/commit/69475dc102c425ee8bd38536d42631a55141fc56))

## [9.14.0](https://github.com/labor-digital/typo3-better-api/compare/v9.13.0...v9.14.0) (2020-06-18)


### Features

* **TypoLink:** implement option to disable cHash for certain link args ([db7d0d5](https://github.com/labor-digital/typo3-better-api/commit/db7d0d54fde0b24e2f9bda09e825ee4a502d806f))
* make code psr-2 compliant ([5b2883b](https://github.com/labor-digital/typo3-better-api/commit/5b2883b77934fe9f10ffb117a5df61c82248ee36))


### Bug Fixes

* **RouteEnhancer:** make sure that requirements for route enhancers are registered correctly ([0924f49](https://github.com/labor-digital/typo3-better-api/commit/0924f490c2a71e70a3921f447cfb6d27a602c04a))

## [9.13.0](https://github.com/labor-digital/typo3-better-api/compare/v9.12.2...v9.13.0) (2020-05-28)


### Features

* **BackendForm:** implement new logic abstracts ([d328f42](https://github.com/labor-digital/typo3-better-api/commit/d328f422f17c6d455e7104ba90d214cdde4a729e))


### Bug Fixes

* **Typoscript:** fix issue when the typoscript is empty ([cd452fe](https://github.com/labor-digital/typo3-better-api/commit/cd452fe7dea123ff0fd21348466dc481ec4cef23))

### [9.12.2](https://github.com/labor-digital/typo3-better-api/compare/v9.12.1...v9.12.2) (2020-05-25)


### Bug Fixes

* minor fixes after git mixup - sorry ([2e599d7](https://github.com/labor-digital/typo3-better-api/commit/2e599d72784b38a6da3e3ae76198bb724ae465f4))

### [9.12.1](https://github.com/labor-digital/typo3-better-api/compare/v9.12.0...v9.12.1) (2020-05-25)

## [9.12.0](https://github.com/labor-digital/typo3-better-api/compare/v9.11.4...v9.12.0) (2020-05-25)


### Features

* **Event:** update code for new version of the eventbus library ([e9a0487](https://github.com/labor-digital/typo3-better-api/commit/e9a04875efbd7acb054595435645761e5f2f7809))
* add setup for phpunit tests ([29d37ab](https://github.com/labor-digital/typo3-better-api/commit/29d37ab14bec8bb8fefe6afcd1ca6cd8ecf4a76e))
* **ExtConfig:** implement new, faster form node tree ([5793c03](https://github.com/labor-digital/typo3-better-api/commit/5793c03c0019ebbd93b689ea270fbf08501939d9))


### Bug Fixes

* **Log:** make sure the logLevel allows integers as well as strings ([c30747d](https://github.com/labor-digital/typo3-better-api/commit/c30747dddc81de053205ab26ebd4b774b4bdf5ab))

### [9.11.4](https://github.com/labor-digital/typo3-better-api/compare/v9.11.3...v9.11.4) (2020-05-22)


### Bug Fixes

* **StandaloneBetterQuery:** make sure the "update()" method sets the arguments correctly for the doctrine query builder ([6c333c6](https://github.com/labor-digital/typo3-better-api/commit/6c333c60951aba9125a4d3ce1a8391e9bb6d2bf7))

### [9.11.3](https://github.com/labor-digital/typo3-better-api/compare/v9.11.2...v9.11.3) (2020-05-20)


### Bug Fixes

* make sure flex form IRRE relations work correctly ([98fb36b](https://github.com/labor-digital/typo3-better-api/commit/98fb36bb31042833abd191cec15d2accab88c1b0))

### [9.11.2](https://github.com/labor-digital/typo3-better-api/compare/v9.11.1...v9.11.2) (2020-05-18)


### Bug Fixes

* **ExtendedReferenceIndex:** fix issue when deleting files ([6bb883e](https://github.com/labor-digital/typo3-better-api/commit/6bb883ee1730aa62e82dd1fb37bd00e8cbf225af))

### [9.11.1](https://github.com/labor-digital/typo3-better-api/compare/v9.11.0...v9.11.1) (2020-05-12)


### Bug Fixes

* **RequestFacet:** fix issue when no POST is given ([03b2abc](https://github.com/labor-digital/typo3-better-api/commit/03b2abcb03a02857aecd852a66d35365d650b6e7))

## [9.11.0](https://github.com/labor-digital/typo3-better-api/compare/v9.10.0...v9.11.0) (2020-05-12)


### Features

* **BackendForms:** add "inverted" option to checkbox preset ([bf98203](https://github.com/labor-digital/typo3-better-api/commit/bf982033ade22946b0a933eefcf4e3c6170fea23))
* **BackendForms:** update default TCA definition and remove no longer required sql definitions ([6218092](https://github.com/labor-digital/typo3-better-api/commit/6218092a0aadce942a1d73cdebf3c1ed89c06660))
* remove deprecated dependencies ([54e6ace](https://github.com/labor-digital/typo3-better-api/commit/54e6acea61d1f3162fdc8b024e5432a81356deac))

## [9.10.0](https://github.com/labor-digital/typo3-better-api/compare/v9.9.1...v9.10.0) (2020-05-12)


### Features

* implement new typo context architecture ([1eb763a](https://github.com/labor-digital/typo3-better-api/commit/1eb763a53dcf75d358cca0e05c4bf4a1b270a0b9))
* implement replacement for common service locator trait ([09f77c2](https://github.com/labor-digital/typo3-better-api/commit/09f77c2dc6161497a9614fa8b93e0cf4f3ae4542))


### Bug Fixes

* remove debug output ([e837418](https://github.com/labor-digital/typo3-better-api/commit/e837418e7a87638763c1dfd0071602736c3e818d))
* **SiteAspect:** fix infinite recursion loop issue ([50431b7](https://github.com/labor-digital/typo3-better-api/commit/50431b755df7a930067d5b45beb598bbbea5db1e))

### [9.9.1](https://github.com/labor-digital/typo3-better-api/compare/v9.9.0...v9.9.1) (2020-05-11)


### Bug Fixes

* **SiteAspect:** make sure we can handle the sites even if we don't have a request object ([bb29f56](https://github.com/labor-digital/typo3-better-api/commit/bb29f56ea2d7705f0cb5d1377c68ae66ad55208e))

## [9.9.0](https://github.com/labor-digital/typo3-better-api/compare/v9.8.0...v9.9.0) (2020-05-11)


### Features

* **SiteAspect:** make site detection more reliable ([e759715](https://github.com/labor-digital/typo3-better-api/commit/e759715ec602d418ed73b6b40a487d6653f49a87))


### Bug Fixes

* **PageService:** fix crash when the rootline is requested with no pages ([aefc69e](https://github.com/labor-digital/typo3-better-api/commit/aefc69ecd8c9ca0d17018149ea439e6eea59409e))

## [9.8.0](https://github.com/labor-digital/typo3-better-api/compare/v9.7.4...v9.8.0) (2020-04-30)


### Features

* implement ConfigRepository as central knowledge base for different kinds of information ([581180f](https://github.com/labor-digital/typo3-better-api/commit/581180fa26c2406ab67a12112ac41b684abe1439))

### [9.7.4](https://github.com/labor-digital/typo3-better-api/compare/v9.7.3...v9.7.4) (2020-04-21)


### Bug Fixes

* **ExtBaseOption:** don't remove plugin|ext|config|configuration from the end of a plugin controller's class name ([98b0a82](https://github.com/labor-digital/typo3-better-api/commit/98b0a8287509a5f592ff78f64889750807197c45))

### [9.7.3](https://github.com/labor-digital/typo3-better-api/compare/v9.7.2...v9.7.3) (2020-04-19)


### Bug Fixes

* **EnvironmentSimulator:** make sure the pid array is restored after the simulation ends ([29288e5](https://github.com/labor-digital/typo3-better-api/commit/29288e56de5be7a5e000ea9977cb5bc64589fddb))

### [9.7.2](https://github.com/labor-digital/typo3-better-api/compare/v9.7.1...v9.7.2) (2020-04-17)


### Bug Fixes

* fix a typo in pidAspect ([1e3916d](https://github.com/labor-digital/typo3-better-api/commit/1e3916dbd057c48087c33f83b1b6b954acb27d11))

### [9.7.1](https://github.com/labor-digital/typo3-better-api/compare/v9.7.0...v9.7.1) (2020-04-17)


### Bug Fixes

* **ResizedImagesOptionsTrait:** make sure number values are parsed as number if possible ([6b47351](https://github.com/labor-digital/typo3-better-api/commit/6b47351a5095e98cdabcbfa667407ccca8d0edb2))

## [9.7.0](https://github.com/labor-digital/typo3-better-api/compare/v9.6.0...v9.7.0) (2020-04-15)


### Features

* implement new RefIndexRecordDataFilterEvent event to filter the ref index record before the index is generated ([2f3b9f2](https://github.com/labor-digital/typo3-better-api/commit/2f3b9f25a86f39fe022630e9e727ff4a25eb364e))

## [9.6.0](https://github.com/labor-digital/typo3-better-api/compare/v9.5.2...v9.6.0) (2020-04-11)


### Features

* **FalFileService:** improved handling when generating resized images + externalizing resized image options for other extensions ([8faa446](https://github.com/labor-digital/typo3-better-api/commit/8faa44625042a47f9788e49257c7abed30300f29))

### [9.5.2](https://github.com/labor-digital/typo3-better-api/compare/v9.5.1...v9.5.2) (2020-04-08)


### Bug Fixes

* **RelationPreset:** make sure the image crop variants are validated correctly ([0cb7720](https://github.com/labor-digital/typo3-better-api/commit/0cb7720b890ac9af6b860f3c9f43bfebb72ddded))
* **RelationPreset:** remove debug output ([80d2c08](https://github.com/labor-digital/typo3-better-api/commit/80d2c086d32bacd392beba523482d4f61ace3c52))

### [9.5.1](https://github.com/labor-digital/typo3-better-api/compare/v9.5.0...v9.5.1) (2020-04-08)


### Bug Fixes

* **VarFs:** only flush the directory when the "all" cache is cleared. ([3e9c5f3](https://github.com/labor-digital/typo3-better-api/commit/3e9c5f357197467485f072be4dd0740ca129d35b))

## [9.5.0](https://github.com/labor-digital/typo3-better-api/compare/v9.4.1...v9.5.0) (2020-04-07)


### Features

* **FileAndFolder:** add FileInfo class as successor to FalFileService::getFileInformation() ([64c1ec5](https://github.com/labor-digital/typo3-better-api/commit/64c1ec5bfe6ce36b37983fe924468fa0e5f468be))
* **SiteAspect:** more reliable detection for the site even if it was not explicitly defined ([685fdd8](https://github.com/labor-digital/typo3-better-api/commit/685fdd8933f3b02f4886753378f641b11bc9dbeb))


### Bug Fixes

* **LinkService:** use fallback host lookup if no request exists ([91cf470](https://github.com/labor-digital/typo3-better-api/commit/91cf470b1ca96147779d4eda4327487072aa80a2))

### [9.4.1](https://github.com/labor-digital/typo3-better-api/compare/v9.4.0...v9.4.1) (2020-03-30)


### Bug Fixes

* **BetterQuery:** avoid an issue where old table alias fragments were added when multiple queries were executed in sequence ([81b2596](https://github.com/labor-digital/typo3-better-api/commit/81b2596fccc9a5d9087c0ba001c16a55214303cf))

## [9.4.0](https://github.com/labor-digital/typo3-better-api/compare/v9.3.0...v9.4.0) (2020-03-29)


### Features

* **LinkService:** add getTypoLinkTarget() helper to extract the link target from a typo link definition ([b23aa0d](https://github.com/labor-digital/typo3-better-api/commit/b23aa0da6f39612137a47b92ef82338ee31cdbe8))
* **PageService:** extend getPageContents() to return the raw page contents if requested ([dd7b118](https://github.com/labor-digital/typo3-better-api/commit/dd7b1180db2e02f5a9954b12e4dd629e28ee878e))


### Bug Fixes

* **BackendListLabelFilterEventAdapter:** make sure BackendListLabelFilterEvent only receives arrays even on new records ([4212d52](https://github.com/labor-digital/typo3-better-api/commit/4212d52100699a9d78b0f36b34e31ad5aea33b3d))
* **DataHandlerActionService:** use the correct event data before reinjecting "databaseRow" on the form filter event ([f624928](https://github.com/labor-digital/typo3-better-api/commit/f62492890a4150f584888d1e7ed1790e8a8cfd89))

## [9.3.0](https://github.com/labor-digital/typo3-better-api/compare/v9.2.1...v9.3.0) (2020-03-27)


### Features

* **BackendPreviewService:** allow overrides for already rendered previews with registered renderers ([cbd6dfb](https://github.com/labor-digital/typo3-better-api/commit/cbd6dfb8f662a42d9cd7eed00bcc1dc8b9a48d5b))


### Bug Fixes

* **BackendPreviewRenderingEvent:** make sure the event is dispatched ([b86e3f3](https://github.com/labor-digital/typo3-better-api/commit/b86e3f3eed92bf83e167d9aaf643f42fcc3012b7))
* **BackendPreviewService:** don't set a backend preview as rendered if the result of the handler is empty ([0f5853b](https://github.com/labor-digital/typo3-better-api/commit/0f5853b95052f223d523aeea17b2638526bec48f))
* **BackendPreviewService:** make sure the default header is set before the handler is executed ([9a3979b](https://github.com/labor-digital/typo3-better-api/commit/9a3979bc90137463798126674cc7a65727cf0ed3))
* fix action handler registration for ext base plugins ([71ab893](https://github.com/labor-digital/typo3-better-api/commit/71ab8936c2deb04ffacd94f63c5744fb81bc8c44))

### [9.2.1](https://github.com/labor-digital/typo3-better-api/compare/v9.2.0...v9.2.1) (2020-03-26)


### Bug Fixes

* **DataHandlerRecordInfoFilterEvent:** make sure that id can also be a string ([bbbd25b](https://github.com/labor-digital/typo3-better-api/commit/bbbd25b58f937f112e808ceb03a4c642a83853da))
* **FieldPreset:** make sure useNativeElement on slug fields works correctly ([aa5254c](https://github.com/labor-digital/typo3-better-api/commit/aa5254ca267b0f479b310b335e53cbce62ad6b74))
* **InternalAccessTrait:** make hasMethod() public as it should be ([210e274](https://github.com/labor-digital/typo3-better-api/commit/210e274a0d326c8d091ddd00e8b9b7f3c38a36e3))
* **LinkSetDefinition:** make sure cHash and keepQuery parameters are given to $link even if they are set to FALSE ([727a4d8](https://github.com/labor-digital/typo3-better-api/commit/727a4d8d7acb60027634f5a0465f6f982ebc22a7))
* **StandaloneBetterQuery:** rename findRelated to getRelated to keep the code consistent ([7205038](https://github.com/labor-digital/typo3-better-api/commit/720503810e53a79848970f0c13bda6be062bf3b5))
* **VarFs:** fix relative path issue with getBaseDirectoryPath() ([0d624c0](https://github.com/labor-digital/typo3-better-api/commit/0d624c0fdcfb4a89c3dac90a09536666d0f691f9))
* **TypoScriptService:** make sure dynamic typo script files are always written even if they have no content ([30b0179](https://github.com/labor-digital/typo3-better-api/commit/30b0179892d3cebb52de8d5260b72eeeaf01305d))

## [9.2.0](https://github.com/labor-digital/typo3-better-api/compare/v9.1.4...v9.2.0) (2020-03-25)


### Features

* **DisplayConditionTrait:** add "auto-add" when multiple display conditions are passed as array ([47138ae](https://github.com/labor-digital/typo3-better-api/commit/47138ae4c8a585c7352cdb92e8b2d04bf697c0d9))
* **FormPreset:** add "prepend_tname" to the field config when an mm table is generated ([1159eb1](https://github.com/labor-digital/typo3-better-api/commit/1159eb1a68b47f9fe675944810e58611e1985b06))
* **StandaloneBetterQuery:** add findRelated() method to find related records for a query + make sure the translation and versioning is applied to query results ([8640376](https://github.com/labor-digital/typo3-better-api/commit/8640376d3f2853c9e85d7a797176679bc85f50a0))


### Bug Fixes

* make sure the dynamic traits are compiled correctly even if there is a change in the extension list ([0432394](https://github.com/labor-digital/typo3-better-api/commit/0432394e36ef70469e2af41d427c2139164dff79))
* **TcaTable:** make sure "null" type keys are converted to 0 ([a7ded5d](https://github.com/labor-digital/typo3-better-api/commit/a7ded5dc74f4605956272a0467c58190fc96e35a))
* add data handler action handlers to the correct tca array when building a tca table ([e23911d](https://github.com/labor-digital/typo3-better-api/commit/e23911d6a234be9480348f45ec31f7a8fde613c6))
* remove "NOT NULL" sql statement to keep the sql compatible with sql server ([e10e943](https://github.com/labor-digital/typo3-better-api/commit/e10e9438ed68a0cd862c58163d7c7484957e0f0a))
* return $clone in AbstractBetterQuery::withOrder instead of $this ([10496bd](https://github.com/labor-digital/typo3-better-api/commit/10496bd3b3e266c3785181704b33fd966ba3e62d))
* set setReloadOnChange() value on a form field to TRUE by default ([f1c9996](https://github.com/labor-digital/typo3-better-api/commit/f1c9996c08805f725671a15620b6a337e18c7d7e))

### [9.1.4](https://github.com/labor-digital/typo3-better-api/compare/v9.1.3...v9.1.4) (2020-03-24)


### Bug Fixes

* fix broken tca type generation after new event bus implementation ([844952e](https://github.com/labor-digital/typo3-better-api/commit/844952ef754b53dd9c86130a84d739c33360ef79))
* fix incompatibility with public debugger implementation ([d995dca](https://github.com/labor-digital/typo3-better-api/commit/d995dca2fa5d165610422c94bda1556bdd0a7277))
* fix incorrect ordering of form elements when "before" or "after" are used as modifiers and the target is a "tab" ([e317a77](https://github.com/labor-digital/typo3-better-api/commit/e317a770d4abe3b88237ffdb224dacaed6fbff57))
* remove "NOT NULL" sql statement to keep the sql compatible with sql server ([44a1280](https://github.com/labor-digital/typo3-better-api/commit/44a1280ef69a3204790641413e717128a4844e83))

### [9.1.3](https://github.com/labor-digital/typo3-better-api/compare/v9.1.2...v9.1.3) (2020-03-23)


### Bug Fixes

* reimplement hook extension to make sure the events are triggered correctly when using the "nicer" way to register ourselves ([264cf68](https://github.com/labor-digital/typo3-better-api/commit/264cf685b018fad5c59f03d7c38a877a8cb18d41))

### [9.1.2](https://github.com/labor-digital/typo3-better-api/compare/v9.1.1...v9.1.2) (2020-03-23)


### Bug Fixes

* add more elegant self activation ([e03945e](https://github.com/labor-digital/typo3-better-api/commit/e03945e0f0ff680baa3bd0cdca90183cce84921c))

### [9.1.1](https://github.com/labor-digital/typo3-better-api/compare/v9.1.0...v9.1.1) (2020-03-23)


### Bug Fixes

* make dependency versions specific ([1f3b9b3](https://github.com/labor-digital/typo3-better-api/commit/1f3b9b3ba5011e68771a442f0195e751488107dd))
* make sure the package is correctly registered before other packages can depend on it ([5f04c35](https://github.com/labor-digital/typo3-better-api/commit/5f04c35ee56858bbc94b1d0749cc0a78224f5a63))

## 9.1.0 (2020-03-22)


### Features

* first public release ([419efe7](https://github.com/labor-digital/typo3-better-api/commit/419efe7ccb0495cbc5601b2ef8fa64b106457bad))

## [2.18.2] (2020-02-05)


### Bug Fixes

* **EnvironmentSimulator:** check availability before backing up language and request objects ([336cc80])
* **TemplateRendering:** reimplement first level renderer cache for handlebar templates ([497dbb2])



## [2.18.1] (2020-02-04)


### Bug Fixes

* **AbstractElementConfigurator:** allow markers in extBase plugin icon path ([c19cd67])
* **AbstractFormField:** fix namespace of form preset applier class ([e9f916b])
* **ExtBaseOption:** keep "module" at the end of backend module controller names ([ba090e0])
* add missing translations strings ([dcd6e5c])



# [2.18.0] (2020-01-31)


### Bug Fixes

* **ExtConfig:** fix issue with broken cache lookups by removing the cacheKeyBase property ([af558a0])
* **FlexForms:** inherit the correct config when building the sections ([aea28f2])
* **PageService:** add where clause to avoid duplicate elements when rendering the page content ([5bce701])
* **TemplateRenderingService:** remove first level renderer cache for mustache templates to avoid issues when the renderers are executed multiple times ([fbc8509])
* minor bugfixes and adjustments ([b7b4ceb])


### Features

* **BackendForms:** add date eval and add eval options to more field presets ([c7ca762])
* **BackendForms:** add display conditions to flex form sections ([97c27e5])
* **TcaForms:** fix missing label bug in palettes ([0af2eed])



## [2.17.1] (2020-01-23)


### Bug Fixes

* **BackendForms:** fix an issue with the table sql generation if all fields are set to "use no sql column" + add "null" as input eval statement ([310298b])
* **BackendForms:** make sure linebreaks in table types are not handled like fields ([ca5d825])
* **TranslationLabelProvider:** make sure the translation label gets trimmed before it is translated ([93ed088])



# [2.17.0] (2020-01-20)


### Bug Fixes

* **BasicFieldPreset:** fix wrong variable lookup when setting the rte configuration ([b54dc55])


### Features

* **ExtConfig:** add infrastructure to easily implement child-ext-config objects ([bf7ccd3])
* **FileAndFolder:** add description to image file information arrays ([7bdf109])
* **FormPresets:** add placeholder option to input field ([ef95767])
* **Rendering:** Allow partial rendering in mustache templates ([e8b3dc4])
* **SiteAspect:** make site resolving more resilient ([bd6999d])



## [2.16.1] (2020-01-15)


### Bug Fixes

* **BackendForms:** implement some bugfixes for element removal and type-palette retrieval ([8ce98db])



# [2.16.0] (2020-01-10)


### Bug Fixes

* **TypoContext:** make sure we don't interfere with the context creation when using our TypoContext class ([aba96b1])


### Features

* merge in some features and fixes that I did on the v8/v7 branch ([27ef4c6])



# [2.15.0] (2019-12-09)


### Features

* **Cache:** add support for cache tags in the typo cache interface ([d1ea85a])



# [2.14.0] (2019-12-06)


### Bug Fixes

* **BetterQuery:** fix issue when a site language object was passed as language id ([76dce9c])
* **LinkService:** build the correct host even when in backend ([07d5433])


### Features

* **Cache:** make the getTypoCache() method public on all cache implementations ([cb7d8e4])
* **Event:** add new EXTBASE_PERSIST_POST_PROCESSOR event and allow for static event -> slot rewrite ([372e057])
* **PageService:** make getPageRepository part of the public API ([af8772c])



## [2.13.1] (2019-12-03)


### Bug Fixes

* **Error:** fix issue where the production exception handler rendered the debug exception output instead ([9fc34a2])



# [2.13.0] (2019-12-02)


### Features

* **BackendForms:** automatically define all new TCA fields as "exclude" => 1 as there should be no reason not to ([8d1432c])



# [2.12.0] (2019-11-28)


### Features

* **FalFileService:** add youtube video id to the file information array ([d94bd4f])



# [2.11.0] (2019-11-27)


### Features

* remove EVENT_ prefix in ExtConfigOptionEventList class ([8e2621a])
* update to latest labor/typo3-better-api-composer-plugin version ([67a6223])



# [2.10.0] (2019-11-27)


### Bug Fixes

* use legacy language service to avoid compatibility issues with other extensions ([6279106])
* **BackendForms:** fix DbBaseIdApplier to apply the base id multiple times when running with Typo v9's form enhancements ([30a454d])
* **BackendForms:** make sure that basePid's also work in flex form fields ([fd54845])
* **FalFileService:** make sure that external resources that return a full url don't get wrapped in another url ([f88d2a4])
* **TranslationService:** Use legacy language service to avoid compatibility issues with other extensions ([7a2d202])


### Features

* add new ExtendedSiteConfiguration core override to allow filtering of the site configuration files when they are loaded from yaml ([4961100])
* add new http ext config option to configure middlewares, route enhancers and other http related settings ([d5d6903])
* **BackendForms:** finalize slug form element ([37c46bb])
* **BackendForms:** remove BackendFormEventHandler and move the code to the BetterApiInit class. Keep CustomElementContextFilter as event handler to filter custom element contexts ([274cbaf])
* **BetterRepository:** add getTableForModel() to allow repositories to look up table names for models or class names ([a9fd72a])
* **PathAspect:** add new getSlugFor function to generate a slug for a database record ([017e9cc])
* remove EVENT_ prefix in eventList interface + minor documentation and deprecation updates ([9235fa8])
* remove the redundant EVENT_ prefix for all EventList interfaces ([7e9b9b2])
* split DefaultFormFieldPresets mega-class up to more readable sub-classes ([abea4ad])



# [2.9.0] (2019-11-21)


### Bug Fixes

* **BackendForms:** fix issues where baseDir config option for fal relations was not working ([78378e5])
* **BackendForms:** remove palettes from showitem definition after the last item was removed ([1b7a1bc])
* **CustomWizard:** fix issues with custom wizard implementation in Typo3 v9 and up ([8c29b37])
* **Log:** make sure the "filesToKeep" option is passed to the better file writer ([c6889df])


### Features

* **BackendForms:** add relationOnlineMedia() preset to the form field preset list ([f929bc0])
* **BetterQuery:** add option to allow the retrieval of hidden / deleted relation objects in a better query context ([31bf43d])
* **Log:** add log configuration option and better file writer to automatically handle log rotation ([97ccd54])
* **TypoContext:** allow access on user objects using the context aspects + remove dependencies on the commonServiceLocatorTrait ([c93c950])
* update helferlein dependency ([cbe9def])



# [2.8.0] (2019-11-16)


### Bug Fixes

* make sure the tca migration check in the install tool works as expected ([fc2ac0e])
* **BackendForms:** fix some issues with the TCA that required typo3 to apply migrations ([7942cf0])
* **BackendForms:** make sure the correct extConfigContext object is passed to the field preset applier ([40aca0b])
* **BetterApiPreparation:** make sure to read the "pages" value from the row and not from the settings ([64b4beb])


### Features

* **BackendForms:** add support for "readOnly" preset in input fields ([e4a8ff7])
* **BackendForms:** enable mm tables only if they are required ([2544c0d])
* **BetterEntity:** add pid configuration support to setPid() ([1c25b36])
* **Event:** add event when a typo3 error occurs ([cafa05a])
* **Event:** replace more event strings with event list constants ([a57b4c6])
* **Simulation:** add support for hidden page/content rendering when using the environment simulator + add better visibility aspect to typo context ([477dd30])
* update dependencies ([6068528])



## [2.7.1] (2019-11-11)


### Bug Fixes

* **SiteAspect:** fix issue with site aspect when browsing a folder in the FAL backend view ([98a7b59])



# [2.7.0] (2019-11-11)


### Features

* **BackendForms:** remove SelectElementsInNewSectionsCanTriggerReloadApplier addon as it is no longer required in TYPO3 v9 ([0af5691])



# [2.6.0] (2019-11-08)


### Bug Fixes

* **FalFileService:** fix size definition in file information array when using a processedFile object ([16f6f3c])


### Features

* implement handling of image file cropping and crop variants in fal file service and form fields ([90bc8c8])



# [2.5.0] (2019-11-08)


### Bug Fixes

* **BackendForms:** fix issue when moving palettes between tabs ([fad2fcd])
* **BetterQuery:** fix issue with stupid annotation nonsense ([1bdcaa2])


### Features

* **BackendForms:** add description configuration ([0299582])
* **CommonServiceLocator:** add frontend and general cache to service list ([f3525a2])
* **ExtConfig:** add support for symfony commands and rte configuration registration ([69fb0d5])
* add more event definitions using the event list interfaces ([c2e9d0b])



# [2.4.0] (2019-10-27)


### Features

* **ClassOverrideGenerator:** add hook to modify the contents of the generated class alias and clone contents ([d8ec6f0])



# [2.3.0] (2019-10-25)


### Features

* **BetterLanguageAspect:** add method to get all frontend language objects as array ([9d56992])
* **ExtConfigContext:** allow to call runWithExtKeyAndVendor without vendor name ([e79913a])
* **FlexForm:** add flex form field support for fields that are not correctly wrapped in the TCEforms node ([7d42947])
* **RequestAspect:** remove local request storage and use the reference in $GLOBALS instead ([42eef73])
* add failsafe wrapper to avoid exceptions in our code when typo3 runs in a failsafe mode (install tool) ([a47401f])
* add support for typo3console extension ([22c68ca])
* update dependencies ([0cba311])



# [2.2.0] (2019-09-25)


### Features

* implement changes required to run in a TYPO3 v9 eco system ([c17305c])



## [2.1.1] (2019-09-25)


### Bug Fixes

* update composer plugin version ([d516230])



# [2.1.0] (2019-09-25)


### Features

* update dependencies ([db81246])



# [2.0.0] (2019-09-13)


### Features

* add composer plugin dependency for V9 and up ([4777073])


### BREAKING CHANGES

* The package is now only valid for typo3 9 and up



# [1.5.0] (2019-09-11)


### Bug Fixes

* **Language:** make injected default language config less obstructive ([9e778d9])


### Features

* **TypoScript:** add additional comments to show what lead to the ts code in the config ([6563338])



# [1.4.0] (2019-09-10)


### Bug Fixes

* minor adjustments and documentation ([af154e9])


### Features

* **ExtConfig:** add more reliable ext config class sorting and extract it into a separate class ([ac8704b])
* **Pid:** pass the pid storage through typoscript to allow page based overrides of pids ([9d0889a])



# [1.3.0] (2019-09-09)


### Bug Fixes

* remove remaining x dependencies ([f8db404])
* **BackendForms:** make sure that select boxes only get the fallback onChange handler if they are in new flex form sections ([980124c])
* **BetterQuery:** allow ObjectStorage objects as array-like elements in a query ([b575f92])
* **Db7:** make sure that getRecords() does not crash when a NULL is given as $uid ([afc4fe3])


### Features

* **BackendForms:** add multiple features that were no longer supported after the drop of the typo-base extension (default value translation, base pid's and base directories). Added extended filtering of form nodes. ([a392bfa])
* **BackendForms:** make sure that the customElement / customWizard configuration is done statically ([56cc538])
* allow translation of placeholders in tca fields as well ([0520786])
* **BackendForms:** re-add v7 bugfix to allow on change functions on most of the tca elements ([54284d7])
* **BackendForms:** re-adds bugfix that allows select fields to trigger an "on-change" reload in typo3 7.6 ([c2f6e7d])
* **ChangeFunctionBuilder:** Better implementation to handle flex form sections correctly ([b3b4a27])
* **CustomWizard:** add the option to register custom wizard classes ([6965b6c])
* **TcaForms:** add getters and setters for the type column ([38f1578])
* **TcaTableType:** allow cloning of an entire tab from the base type to a child type ([610211f])
* update helferlein-php dependency ([d8331a0])



# [1.2.0] (2019-09-07)


### Bug Fixes

* add table back to method parameters ([ed8aca2])
* harden the implementation of the backend tree node filter adapter ([277253a])
* **BackendPreview:** add correct default, typoscript value in ExtBaseBackendPreviewRendererTrait ([b00face])
* **ClassOverrideGenerator:** Make [@see] $originalClass clickable in IDE ([32b9d16])


### Features

* rename TableConfigInterface->configure to configureTable and make the method static ([f92137e])
* **FalFileService:** add additional fal actions, like handling file, file reference and folder creation using the fal service ([8b8b561])
* **TcaTable:** add option to configure the tca table sort order in list view mode from the ext config options and the tca table facade ([e24ca5b])



# [1.1.0] (2019-09-06)


### Bug Fixes

* **CustomElements:** remove deprecated nodeFactory from custom element classes ([dae4906])
* minor adjustments ([da5f74d])


### Features

* **BackendPreview:** better implementation for the backend rendering of ext base plugins + allow edit link creation ([cf29648])
* **FalFileService:** Allow image url creation for resized images ([78b6213])
* update dependencies ([1db61ae])



## [1.0.2] (2019-09-01)


### Bug Fixes

* fix broken composer autoload path and remove legacy typo base references ([b28930d])



## [1.0.1] (2019-09-01)
