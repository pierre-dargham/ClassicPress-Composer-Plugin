<?php

namespace ClassicPress\Composer;

use Composer\Package\PackageInterface;
use Composer\Installer\LibraryInstaller;
use Composer\Repository\InstalledRepositoryInterface;

class CoreInstaller extends LibraryInstaller
{
    const TYPE                 = 'classicpress-core';
    const EXTRA_KEY            = 'classicpress-core-install-path';
    const DEFAULT_INSTALL_PATH = 'classicpress';

    /**
     * {@inheritDoc}
     */
    public function supports($packageType)
    {
        return self::TYPE === $packageType;
    }

    /**
     * {@inheritDoc}
     */
    public function install(InstalledRepositoryInterface $repo, PackageInterface $package)
    {
        $this->initializeVendorDir();
        $downloadPath = parent::getInstallPath($package);

        $this->downloadManager->download($package, $downloadPath);

        if (!$repo->hasPackage($package)) {
            $repo->addPackage(clone $package);
        }

        $this->filesystem->copy($downloadPath . '/src', $this->getInstallPath($package));
        $this->filesystem->remove($downloadPath);
    }

    /**
     * {@inheritDoc}
     */
    public function getInstallPath(PackageInterface $package)
    {
        if ($rootPackage = $this->composer->getPackage()) {
            $extra = $rootPackage->getExtra();

            if (!empty($extra[self::EXTRA_KEY])) {
                return $extra[self::EXTRA_KEY];
            } elseif (!empty($extra['wordpress-install-dir'])) {
                return $extra['wordpress-install-dir']; // Backward compatibility
            }
        }

        return self::DEFAULT_INSTALL_PATH;
    }
}
