<!-- START_METADATA
---
title: Vipps/MobilePay Login for Adobe Commerce user guide
sidebar_label: User guide
sidebar_position: 30
hide_table_of_contents: true
pagination_next: null
pagination_prev: null
---
END_METADATA -->

# User guide

## Introduction

The Vipps MobilePay Login module offers functionality for authenticating end users and authorizing clients.
It is founded on the OAuth2 and OpenID Connect specifications
and supports using web browsers on websites and in native apps for iOS and Android using app switching.

## Basic Flow

### Sign-In using Vipps / Register using Vipps

The sequence of operations is as follows:

1. The customer presses a *Sign-In with Vipps* or *Register with Vipps* button and is redirected to a Vipps web page.
1. The customer interacts with Vipps web page and mobile app to permit access to Vipps account data for the webshop.
1. The customer is redirected back to Magento webshop.
1. If access was granted to the webshop, the plugin has access to customer data such as: name, email, phone number, and addresses.
1. Magento performs the log-in or creates a new account for the customer.

    If the customer has granted consent for Magento to use their phone number, Magento can then use the Vipps MobilePay Login module to check if the account exists.
    Magento checks if the system already contains an account that is linked with the Vipps account. If yes, the customer is already verified and the log-in can be completed.
    If no, it can mean one of the following:

    * The customer's account has not been previously linked with Vipps. They will need to confirm or link accounts.
    * The customer does not have an account and must create one.

### Confirm existing account

In the case when a customer doesn't have a Magento account which has been previously linked with Vipps,
Magento will try to find existing Magento account by using the email and phone number obtained from Vipps.
If the account exists, it will redirect customer to *Confirmation* page.

The customer can finish the process by using password or email confirmation.
When the account is confirmed, it will be linked with Vipps account. Thus, no extra steps are needed for a later sign-in.

<!--![Confirmation page](docs/images/confirmation.png)-->

### Create a new account

If there is no account defined for customer then Magento will try automatically create a new account using Vipps account data.

In the case when Magento can't create an account automatically, the customer will be redirected to a standard Magento registration form to complete it manually.
This could happen, for example if Magento required additional data for account creation that is missing in Vipps account.

### Link Magento and Vipps accounts

It is possible to link the customer account and Vipps account that is being signed-in into the system.
To do this, customer should:

 - Go to *My Account* page
 - Choose *Login with Vipps* on the left menu
 - Press *Login with Vipps* and finalize the process

<!--![Log in with Vipps](account-login-with-vipps.png)-->

### Sync addresses between Vipps MobilePay and Magento

Each time a customer signs in to the system, the Vipps MobilePay Login module checks if the customer's addresses have changed.

There are three ways to update the addresses and the customer is able to select from these:

 - Update automatically - The customer will not be asked, but addresses will be automatically updated.
 - Ask first - The customer will be asked before the update.
 - Do nothing - The customer will not be asked, and addresses will not be updated.

<!--![Logged-in with Vipps](account-logged-in-with-vipps.png)-->

In the case when a behavior set to *ask first* and the Vipps address(es) where changed, the customer will see a notification.


## Work with addresses

### Get addresses from Vipps account

After log in with Vipps, all your Vipps addresses will be transferred to the Magento webshop and displayed in *My account* / *Address book* left menu item.

The Vipps addresses are stored into a separate `vipps_customer_address` table.

The Vipps addresses are automatically converted to the Magento default billing and shipping address, if such do not exist.

### Default billing and shipping address

The Vipps address that was converted to Magento address is marked as *Applied/Used*.

<!--![applied addresses](account-vipps-addresses-applied.png)-->

### Use Vipps Address

If the Vipps address was not converted to Magento, there will be a link *Use Address*.

<!--![not applied addresses](account-vipps-addresses-not-applied.png)-->

By selecting *Bruk adresse* ("Use address"), the customer will be able to edit the address and save it in a standard Magento way.

<!--![Edit address](account-edit-vipps-address.png)-->

### Adding new Magento address

If customer is adding an address in a standard Magento way and there is at least one Vipps address that was not converted to Magento (was not applied | not using), then customer will be able to use their Vipps address data for new address by choosing it in a dropdown menu at the top.

<!--![Choose address](account-choose-vipps-address.png)-->

NB! If customer changed any data so that newly created address and Vipps address are different such addresses will not be linked between each other.

## Cart page

The Vipps MobilePay module injects a Vipps or MobilePay Login button on the cart page. This allows the customer to log in to the system and proceed to checkout.
This provides a better user experience, since they don't need to specify their shipping or billing address manually.

**Please note:** If the cart page contains an *Express Checkout* button from the Vipps MobilePay Payment module, only the *Login* button will be shown. *Login* has higher priority.

## Checkout page

### Sign-in

It is also possible to sign-in using Vipps or MobilePay from a checkout page.

<!--![Checkout Login](checkout-vipps-login.png)-->

### Adding new address

If the customer wants to add an address directly from the checkout page, you can populate the form based on the unused Vipps addresses.
This is done in the same manner as for *My account* / *Address book* page.

<!--![Checkout new address](checkout-new-address.png)-->
