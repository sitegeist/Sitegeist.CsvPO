<?php

namespace Sitegeist\CsvPO;

use Neos\Flow\Cache\CacheManager;
use Neos\Flow\Core\Booting\Sequence;
use Neos\Flow\Core\Bootstrap;
use Neos\Flow\Monitor\FileMonitor;
use Neos\Flow\Package\Package as BasePackage;
use Neos\Flow\Package\PackageManager;

class Package extends BasePackage
{
    /**
     * Invokes custom PHP code directly after the package manager has been initialized.
     *
     * @param Bootstrap $bootstrap The current bootstrap
     * @return void
     */
    public function boot(Bootstrap $bootstrap)
    {
        $dispatcher = $bootstrap->getSignalSlotDispatcher();

        $context = $bootstrap->getContext();
        if (!$context->isProduction()) {
            $dispatcher->connect(Sequence::class, 'afterInvokeStep', function ($step) use ($bootstrap) {
                if ($step->getIdentifier() === 'neos.flow:systemfilemonitor') {
                    $templateFileMonitor = FileMonitor::createFileMonitorAtBoot('Sitegeist_Translation_Csv_Files', $bootstrap);
                    /**
                     * @var PackageManager $packageManager
                     */
                    $packageManager = $bootstrap->getEarlyInstance(PackageManager::class);
                    foreach ($packageManager->getFlowPackages() as $packageKey => $package) {
                        $fusionDirectory = $package->getResourcesPath() . 'Private/Fusion';
                        if (is_dir($fusionDirectory)) {
                            $templateFileMonitor->monitorDirectory($fusionDirectory, '.*\.csv');
                        }
                    }
                    $templateFileMonitor->detectChanges();
                    $templateFileMonitor->shutdownObject();
                }
            });
        }

        $flushTranslationCaches = function ($identifier, $changedFiles) use ($bootstrap) {
            if ($identifier !== 'Sitegeist_Translation_Csv_Files') {
                return;
            }

            if ($changedFiles === []) {
                return;
            }

            /**
             * @var CacheManager $cacheManager
             */
            $cacheManager = $bootstrap->getObjectManager()->get(CacheManager::class);
            $templateCache = $cacheManager->getCache('Sitegeist_CsvPO_TranslationCache');
            $templateCache->flush();
        };

        $dispatcher->connect(FileMonitor::class, 'filesHaveChanged', $flushTranslationCaches);
    }
}
