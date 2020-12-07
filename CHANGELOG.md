# Changelog

All notable changes to this project will be documented in this file. See [standard-version](https://github.com/conventional-changelog/standard-version) for commit guidelines.

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

* **TempFs:** only flush the directory when the "all" cache is cleared. ([3e9c5f3](https://github.com/labor-digital/typo3-better-api/commit/3e9c5f357197467485f072be4dd0740ca129d35b))

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
* **TempFs:** fix relative path issue with getBaseDirectoryPath() ([0d624c0](https://github.com/labor-digital/typo3-better-api/commit/0d624c0fdcfb4a89c3dac90a09536666d0f691f9))
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
