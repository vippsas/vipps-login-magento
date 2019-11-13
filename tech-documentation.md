## Introduction

The Vipps Login module offers functionality for authenticating end users and authorizing clients founded on the OAuth2 and OpenID Connect specifications. It supports using web browsers on websites and in native apps for iOS and Android using app switching.

## Basic Flow

### Sign-In using Vipps / Register using Vipps

The sequence of operations is as follows:

 - Customer presses a "Sign-In with Vipps" / "Register with Vipps" button
 - Being redirected to Vipps web page customer interacts with Vipps web page and mobile app to permit access to Vipps account data for the webshop
 - Customer redirects back to Magento webshop
 - If access was granted the webshop (Vipps Login plugin) has access to customer Vipps account data such as: name, email, phone number, addresses, etc...
 - Magento does sign-in / create a new account for customer
 
To have a deeper understanding of the process let's consider last item at length.

Firstly, having access to customer's Vipps account data, Magento (the login accumulated into Vipps Login module), using customer's phone number, checks - is there already existing account presents in the system that is linked with Vipps account? 

In case when this check is positive then it means that customer already verified so does sign-in.

In case when check is negative it means that existing customer's account was not linked with Vipps before (customer has to confirm / link accounts) or customer does not have an account (customer has to create an account).

### Confirm existing account

In case when customer does not have a Magento account which has been linked with Vipps before, Magento will try to find existing Magento account using email and phone number obtained from Vipps and if it exists - redirect customer to "Confirmation" page

Customer could finish a process using password or email confirmation. When account confirmed it will be linked with Vipps account, so no extra steps need during next sign-in.


![Screenshot of confirmation page](docs/images/confirmation.png)

    
### Create new account
  
If there is no account defined for customer then Magento will try automatically create a new account using Vipps account data.
   
In case when Magento can't create account automatically, customer will be redirected to standard Magento registration form to complete it manually.
In could happened in case when Magento requires some extra data for account creation that are missing in Vipps account.


### Link Magento and Vipps accounts

It is also possible to link customer account and Vipps account, being signed-in in the system.
To do this customer should do the following:
 - Go to "My Account" page
 - Choose "Login with Vipps" on the left menu
 - Press button "Login with Vipps" and finalize the process 
 
![Screenshot of login with Vipps](docs/images/account-login-with-vipps.png)
    
### Sync addresses between Vipps and Magento

Each time when customer signed-in into the system the Vipps Login module checks whether Vipps customer addresses were changed or not

There are three types of behavior in the module related to addresses update

 - update automatically; customer will not be asked, addresses will be automatically updated.
 - ask first; customer will be asked before update.
 - do nothing; customer will not be asked, addresses will not be updated.
 
![Screenshot of logged-in with Vipps](docs/images/account-logged-in-with-vipps.png)

So, customer could choose type of behavior.


## Work with addresses


## Cart page


## Checkout page




