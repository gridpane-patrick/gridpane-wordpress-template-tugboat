# Taager API

Taager API lets you manage Woocommerce products, orders with Taager.

## Description

This plugin will run the action as following below once at first time.

* import into website's database product categories from Taager
* import into website's database provinces from Taager

This plugin will run the action as following below twice daily.

* import into website's database products from Taager

## Usage

1. Go to `Plugins -> Add New`
2. Click `Upload Plugin` button
3. Upload the plugin zip file from your local and Click `Install Now` button
4. Go to Plugins page once finished installing and you can see a plugin named `Taager API` you just installed
5. Click `Activate` button
6. You can find `Taager API` menu under `Settings` menu in admin menu
7. You can see `Taager API Settings` page if click the `Taager API` menu
8. Enter credentials info to get things from Taager and then click `Save Changes`
9. Then the API will start the importing for Provinces and Categories
10. If the action above has completed, you can see submenu called `Products Setting`
11. If you entered Product Category or Product name and click `Import products`, then the API will start the importing after 1 min at first time and the action will run twice daily
12. Then the API will redirect you to the Woocommerce Products pages

## Addition

If you click `Import products` while runs alraedy existing cron event, then it will redirect to Taager products setting page.
If you want to check for changes in categories or provinces you can go to Taager API page and press on save changes again.
