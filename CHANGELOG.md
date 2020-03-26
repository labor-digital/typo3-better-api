# Changelog

All notable changes to this project will be documented in this file. See [standard-version](https://github.com/conventional-changelog/standard-version) for commit guidelines.

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
