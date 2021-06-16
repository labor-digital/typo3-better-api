# Changelog

All notable changes to this project will be documented in this file. See [standard-version](https://github.com/conventional-changelog/standard-version) for commit guidelines.

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
