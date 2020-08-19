# Vipps Login Module for Magento 2: Installation

# Prerequisites

1. [Magento 2.2](https://devdocs.magento.com/guides/v2.2/release-notes/bk-release-notes.html) or later
1. SSL must be installed on your site and active on your Checkout pages.
1. You must have a Vipps merchant account. See [Vipps logg inn](https://vipps.no/produkter-og-tjenester/bedrift/logg-inn-med-vipps/logg-inn-med-vipps/#kom-i-gang). In our [FAQ](https://github.com/vippsas/vipps-login-api/blob/master/vipps-login-faq.md) you will find information on where you can find required credentials and how to set up Vipps login. For information on Magento configuration and the Redirect URIs to add to the VippsPortal see this [User guide] (https://marketplace.magento.com/media/catalog/product/vipps-module-login-1-3-3-ce/user_guides.pdf)
1. As with _all_ Magento extensions, it is highly recommended to backup your site before installation and to install and test on a staging environment prior to production deployments.

# Installation via Composer

1. Navigate to your [Magento root directory](https://devdocs.magento.com/guides/v2.2/extension-dev-guide/build/module-file-structure.html).
1. Enter command: `composer require vipps/module-login`
1. Enter command: `php bin/magento module:enable Vipps_Login` 
1. Enter command: `php bin/magento setup:upgrade`
1. Put your Magento in production mode if it’s required.

# Configuration

The Vipps Login module can be easily configured to meet business expectations of your web store. This section will show you how to configure the extension via `Magento Admin`.

From Magento Admin navigate to `Store` -> `Configuration` -> `Vipps` -> `Login` section. 

In our [FAQ](https://github.com/vippsas/vipps-login-api/blob/master/vipps-login-faq.md) you will find information on where you can find required credentials and how to set up Vipps login. For information on Magento configuration and the Redirect URIs to add to the VippsPortal see this [User guide] 

Once you have finished with the configuration simply click `Save` button for your convenience. 

# Support

Magento is an open source ecommerce solution: https://magento.com

Magento Inc is an Adobe company: https://magento.com/about

For Magento support, see Magento Help Center: https://support.magento.com/hc/en-us

Vipps has a dedicated team ready to help: magento@vipps.no
