<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;
use App\Models\Product;
use App\Models\CacheStores;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/


Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/clear-cache', function() {
    Artisan::call('cache:clear');
    Artisan::call('config:clear');
    Artisan::call('route:clear');
    Artisan::call('view:clear');

    return response()->json(['message' => 'Cache is cleared']);
});

// Cache clear by product and category wise 
Route::get('/clear-cache-separate', function (Request $request) {
    $type = $request->query('type');
    $slug = $request->query('slug');

    if (!$type || !$slug) {
        return response()->json(['error' => 'Type and slug are required'], 400);
    }

    if ($type === 'product') {
        // Fetch product details
        $product = Product::where('slug', $slug)->first(['id']);

        if (!$product) {
            return response()->json(['error' => 'Product not found'], 404);
        }

        $productId = $product->id;

        // Define all cache keys to delete
        $cacheKeys = [
            "prodetail_{$slug}",
            "prodetail_upsale_{$slug}",
            "prodetail_related_{$slug}",
        ];

        // Fetch and delete extra data keys
        $extraDataKeys = CacheStores::where('key', 'LIKE', "extradatadetail_{$productId}_%")
            ->pluck('key')
            ->toArray();

        $allKeys = array_merge($cacheKeys, $extraDataKeys);

        // Forget all cache keys
        foreach ($allKeys as $key) {
            Cache::forget($key);
        }

        // Remove all related entries from CacheStores table
        CacheStores::whereIn('key', $allKeys)->delete();

        return response()->json(['message' => 'Product cache cleared successfully']);
    }

    return response()->json(['error' => 'Invalid type'], 400);
});

//test
Route::get('/email-testing', [App\Http\Controllers\Api\Frontend\TermsAndConditionsController::class, 'emailSendTest'])->name('api.emailSendTest');
//
Route::customResource('/home_pageData', App\Http\Controllers\Api\HomePageController::class);
Route::customResource('/brands', App\Http\Controllers\Api\BrandApiController::class);
Route::customResource('/sliders', App\Http\Controllers\Api\SliderApiController::class);
Route::customResource('/notifications', App\Http\Controllers\Api\NotificationApiController::class);
Route::customResource('/users', App\Http\Controllers\Api\UserApiController::class);
Route::post('/users/index', [App\Http\Controllers\Api\UserApiController::class, 'index'])->name('usersapi.index');
Route::post('/users/blacklist-index', [App\Http\Controllers\Api\UserApiController::class, 'BlackListIndex'])->name('usersapi.BlackListIndex');
Route::post('/update-blacklist/{id}', [App\Http\Controllers\Api\UserApiController::class, 'updateBlacklist'])->name('usersapi.updateBlacklist');
Route::customResource('/roles', App\Http\Controllers\Api\RoleApiController::class);
Route::customResource('/blogs', App\Http\Controllers\Api\BlogApiController::class);
Route::customResource('/offers', App\Http\Controllers\Api\OffersApiController::class);
Route::customResource('/deals', App\Http\Controllers\Api\DealsApiController::class);
Route::customResource('/menu', App\Http\Controllers\Api\MenuApiController::class);
Route::customResource('/badge', App\Http\Controllers\Api\BadgeApiController::class);
Route::customResource('/app-sliders', App\Http\Controllers\Api\AppSlidersApiController::class);
Route::customResource('/tag', App\Http\Controllers\Api\TagApiController::class);
Route::customResource('/sub-tags', App\Http\Controllers\Api\SubTagsApiController::class);
Route::get('/sub-tagslist/{id}', [App\Http\Controllers\Api\SubTagsApiController::class, 'subTagsList'])->name('api.subTagsList');
Route::customResource('/productcategory', App\Http\Controllers\Api\ProductCategoryApiController::class);
Route::customResource('/product_detail_banner', App\Http\Controllers\Api\ProductDetailBannerApiController::class);
Route::customResource('/promotion_popup', App\Http\Controllers\Api\PromotionPopupApiController::class);
Route::customResource('/promotion_banner', App\Http\Controllers\Api\PromotionBannerApiController::class);
Route::customResource('/free-gift-badge', App\Http\Controllers\Api\FreeGiftBadgeController::class);
Route::customResource('/special-offer', App\Http\Controllers\Api\SpecialOfferController::class);
Route::customResource('/promotion-popup-slider', App\Http\Controllers\Api\PromotionPopupSliderApiController::class);
Route::customResource('/category', App\Http\Controllers\Api\BlogCategoryApiController::class);
Route::customResource('/blogs-sliders', App\Http\Controllers\Api\BlogSlidersApiController::class);
Route::customResource('/shipping_classes', App\Http\Controllers\Api\ShippingClassesApiController::class);
Route::customResource('/taxclasses', App\Http\Controllers\Api\TaxClassesApiController::class);
Route::customResource('/discountcoupon', App\Http\Controllers\Api\DiscountCouponApiController::class);
Route::post('/media-img', [App\Http\Controllers\Api\MediaController::class, 'MediaImageUpload'])->name('MediaImageUpload');
Route::post('/storeMedia', [App\Http\Controllers\Api\MediaController::class, 'storeMedia'])->name('storeMedia');
Route::get('/media/', [App\Http\Controllers\Api\MediaController::class, 'index'])->name('mediaIndex');
Route::get('/media/{id}/delete', [App\Http\Controllers\Api\MediaController::class, 'delete'])->name('mediaDelete');
Route::post('/media/multidelete', [App\Http\Controllers\Api\MediaController::class, 'multidelete'])->name('multidelete');
Route::customResource('/product', App\Http\Controllers\Api\ProductApiController::class);
Route::post('/product/multiimage', [App\Http\Controllers\Api\ProductApiController::class, 'MultipleMediaImageUpload'])->name('multiimage');
Route::customResource('/free_gift', App\Http\Controllers\Api\FreeGiftApiController::class);
Route::customResource('/fbt', App\Http\Controllers\Api\FrequentlyBoughtApiController::class);
Route::customResource('/door_step_delivery', App\Http\Controllers\Api\DoorStepDeliveryApiController::class);
Route::customResource('/regionalmodule', App\Http\Controllers\Api\RegionalModuleApiController::class);
Route::customResource('/expressdelivery', App\Http\Controllers\Api\ExpressDeliveryApiController::class);
Route::customResource('/tax', App\Http\Controllers\Api\TaxApiController::class);
Route::customResource('/abandoned-cart', App\Http\Controllers\Api\AbandonedCartApiController::class);
Route::post('/abanadoned-cart-send', [App\Http\Controllers\Api\AbandonedCartApiController::class, 'abandonedCartSend'])->name('abandonedCartSend');
Route::customResource('/tickets', App\Http\Controllers\Api\TicketApiController::class);
Route::post('/ticket/datastore', [App\Http\Controllers\Api\TicketApiController::class, 'storeduplicate'])->name('ticketduplicatestore');
Route::customResource('/discount_rules', App\Http\Controllers\Api\DiscountRuleApiController::class);
Route::customResource('/alert_message', App\Http\Controllers\Api\AlertMessageApiController::class);
Route::get('/alert_messageindex', [App\Http\Controllers\Api\AlertMessageApiController::class, 'customindex'])->name('alertcustomIndex');
Route::customResource('/region', App\Http\Controllers\Api\RegionApiController::class);
Route::customResource('/shipping_location', App\Http\Controllers\Api\ShippingLocationApiController::class);
Route::customResource('/affiliate_marketing', App\Http\Controllers\Api\AffiliateMarketingApiController::class);
Route::customResource('/shipping-zone', App\Http\Controllers\Api\ShippingZoneController::class);
Route::customResource('/states', App\Http\Controllers\Api\StatesApiController::class);
Route::customResource('/fees', App\Http\Controllers\Api\FeesApiController::class);
Route::customResource('/store_locator', App\Http\Controllers\Api\StoreLocatorApiController::class);
Route::customResource('/warehouse', App\Http\Controllers\Api\WarehouseController::class);
Route::customResource('/department', App\Http\Controllers\Api\DepartmentApiController::class);
Route::customResource('/internal-ticket', App\Http\Controllers\Api\InternalTicketApiController::class);
Route::get('/fetch-user/{number}', [App\Http\Controllers\Api\InternalTicketApiController::class, 'fetchUser'])->name('api.fetchUser');
Route::post('/create-user', [App\Http\Controllers\Api\InternalTicketApiController::class, 'createUser'])->name('api.createUser');
Route::post('/create-order', [App\Http\Controllers\Api\InternalTicketApiController::class, 'createOrder'])->name('api.createOrder');
Route::post('/create-ticket', [App\Http\Controllers\Api\InternalTicketApiController::class, 'createTicket'])->name('api.createTicket');
Route::post('/internal-ticket-img', [App\Http\Controllers\Api\InternalTicketApiController::class, 'InternalTicketMediaImageUpload'])->name('InternalTicketMediaImageUpload');

Route::get('/fetch-tickets/{id}', [App\Http\Controllers\Api\InternalTicketApiController::class, 'fetchTickets'])->name('api.fetchTickets');
Route::get('/fetch-ticket/{userid}/{id}', [App\Http\Controllers\Api\InternalTicketApiController::class, 'fetchTicket'])->name('api.fetchTicket');
Route::get('/update-assignee/{user_id}/{assignee}/{id}', [App\Http\Controllers\Api\InternalTicketApiController::class, 'updateAssignee'])->name('api.updateAssignee');


Route::customResource('/maintenance-center', App\Http\Controllers\Api\MaintenanceCenterController::class);
Route::customResource('/section', App\Http\Controllers\Api\SectionController::class);
Route::customResource('/sla', App\Http\Controllers\Api\SLAApiController::class);
Route::customResource('/product_media', App\Http\Controllers\Api\ProductMediaApiController::class);
Route::post('/productmedia-img', [App\Http\Controllers\Api\ProductMediaApiController::class, 'MediaImageUpload'])->name('ProductMediaImageUpload');
Route::get('/brand/counts', [App\Http\Controllers\Api\BrandApiController::class, 'Countsdatafor'])->name('api.brandcount');
Route::get('/cat/counts', [App\Http\Controllers\Api\ProductCategoryApiController::class, 'catdata'])->name('api.catcount');
Route::get('/products/counts', [App\Http\Controllers\Api\ProductApiController::class, 'prodata'])->name('api.procount');
Route::post('/product/index', [App\Http\Controllers\Api\ProductApiController::class, 'index'])->name('index');
Route::get('/product-copy/{id}', [App\Http\Controllers\Api\ProductApiController::class, 'productCopy'])->name('api.productCopy');
Route::customResource('/coupon', App\Http\Controllers\Api\CouponApiController::class);
Route::customResource('/flash_sale', App\Http\Controllers\Api\FlashSaleApiController::class);
Route::get('/generatecoupon', [App\Http\Controllers\Api\CouponApiController::class, 'couponcode'])->name('api.couponcode');
Route::customResource('/replace_product', App\Http\Controllers\Api\ReplaceProductApiController::class);
Route::customResource('/gift_voucher', App\Http\Controllers\Api\GiftVoucherApiController::class);
Route::customResource('/wallet', App\Http\Controllers\Api\WalletApiController::class);
Route::post('/checksku', [App\Http\Controllers\Api\ProductApiController::class, 'checksku'])->name('api.checksku');
Route::customResource('/popup', App\Http\Controllers\Api\PopupApiController::class);
Route::customResource('/loyality-program', App\Http\Controllers\Api\LoyaltyProgramApiController::class);
Route::get('/productcountincat', [App\Http\Controllers\Api\ProductCategoryApiController::class, 'productcount'])->name('api.getproductcountincat');
Route::customResource('/loyality-settings', App\Http\Controllers\Api\LoyaltySettingApiController::class);
Route::customResource('/wallet-settings', App\Http\Controllers\Api\WalletSettingApiController::class);
Route::customResource('/affiliation', App\Http\Controllers\Api\AffiliationApiController::class);
Route::customResource('/notify_product', App\Http\Controllers\Api\NotifyProductApiController::class);
Route::post('/notify_product/email', [App\Http\Controllers\Api\NotifyProductApiController::class, 'sendEmail'])->name('api.notifyemail');
Route::post('/notify_product/multiemail', [App\Http\Controllers\Api\NotifyProductApiController::class, 'sendMultiEmail'])->name('api.notifymultiemail');
Route::customResource('/general_setting', App\Http\Controllers\Api\GeneralSettingApiController::class);
Route::customResource('/price_alert', App\Http\Controllers\Api\PriceAlertApiController::class);
Route::post('/price_alert/email', [App\Http\Controllers\Api\PriceAlertApiController::class, 'sendEmail'])->name('api.pricealertemail');
Route::post('/price_alert/multiemail', [App\Http\Controllers\Api\PriceAlertApiController::class, 'sendMultiEmail'])->name('api.pricealertmultiemail');
Route::post('/user/login', [App\Http\Controllers\Api\UserApiController::class, 'customLogin'])->name('api.customlogin');
Route::post('/user/forget-password', [App\Http\Controllers\Api\UserApiController::class, 'ForgetPassword'])->name('api.ForgetPassword');
Route::post('/user/reset-password', [App\Http\Controllers\Api\UserApiController::class, 'submitResetPasswordForm'])->name('api.password.post');
Route::post('/user/roles-data', [App\Http\Controllers\Api\UserApiController::class, 'RolesDataPermi'])->name('api.roles.data');
Route::post('/order/index', [App\Http\Controllers\Api\OrderApiController::class, 'index'])->name('api.order-index');
Route::post('/order-send', [App\Http\Controllers\Api\OrderApiController::class, 'orderSend'])->name('orderSend');
Route::post('/send-sms-bulk', [App\Http\Controllers\Api\OrderApiController::class, 'sendSmsBulk'])->name('sendSmsBulk');
Route::post('/get-shipping-calculations', [App\Http\Controllers\Api\OrderApiController::class, 'getShippingCalculation'])->name('getShippingCalculation');
Route::post('/order/{id}/comment', [App\Http\Controllers\Api\OrderApiController::class, 'AddComment'])->name('api.order-comment');
Route::customResource('/order', App\Http\Controllers\Api\OrderApiController::class);
Route::get('trash-order', [App\Http\Controllers\Api\OrderApiController::class, 'trashOrder'])->name('trashOrder');
Route::get('order/{id}/restore', [App\Http\Controllers\Api\OrderApiController::class, 'restoreOrder'])->name('restoreOrder');
Route::customResource('/logs', App\Http\Controllers\Api\LogsController::class);
Route::post('order-boxdata', [App\Http\Controllers\Api\OrderApiController::class, 'boxadditionaldata'])->name('api.additionaldata');
Route::customResource('/product_review', App\Http\Controllers\Api\ProductReviewApiController::class);
Route::post('product_review/status-approve', [App\Http\Controllers\Api\ProductReviewApiController::class, 'ApproveStatus'])->name('api.ReviewApproveStatus');
Route::post('product_review/status-decline', [App\Http\Controllers\Api\ProductReviewApiController::class, 'DeclineStatus'])->name('api.ReviewDeclineStatus');
Route::customResource('/save_search', App\Http\Controllers\Api\SaveSearchApiController::class);
Route::post('order/update-madac', [App\Http\Controllers\Api\OrderApiController::class, 'UpdateMadac'])->name('api.UpdateMadac');
Route::post('user/viewadditionaldata', [App\Http\Controllers\Api\UserApiController::class, 'userviewdata'])->name('api.userviewdata');
Route::post('user/address-order', [App\Http\Controllers\Api\UserApiController::class, 'UserAddressData'])->name('UserAddressData');
Route::get('coupon-counts', [App\Http\Controllers\Api\CouponApiController::class, 'CountsData'])->name('api.couponcount');
Route::customResource('/brand_landing_page', App\Http\Controllers\Api\BrandLandingPageApiController::class);
Route::customResource('/gift-card-setting', App\Http\Controllers\Api\GiftCardSettingController::class);
Route::customResource('/footer_pages', App\Http\Controllers\Api\FooterPagesApiController::class);
Route::customResource('/blog_setting', App\Http\Controllers\Api\BlogSettingApiController::class);
Route::post('order/{id}/update-pending', [App\Http\Controllers\Api\OrderApiController::class, 'UpdatePending'])->name('api.UpdatePending');
Route::post('address-city', [App\Http\Controllers\Api\OrderApiController::class, 'getAddressCity'])->name('getAddressCity');
Route::post('user-addresses', [App\Http\Controllers\Api\UserApiController::class, 'getAllAddresses'])->name('getAllAddresses');
// Route::get('/product-qtycheck', [App\Http\Controllers\Api\ProductApiController::class, 'quantityemail'])->name('quantityemail');
Route::customResource('/maintenance', App\Http\Controllers\Api\MaintenanceApiController::class);
Route::customResource('/user-commission', App\Http\Controllers\Api\UserCommisionApiCotroller::class);
Route::customResource('/loyalty-history', App\Http\Controllers\Api\LoyaltyHistoryApiCotroller::class);
Route::customResource('/email-template', App\Http\Controllers\Api\EmailTemplateApiController::class);
Route::customResource('/tamkeen-premium', App\Http\Controllers\Api\TamkeenPremiumApiController::class);
Route::customResource('/stock-alert', App\Http\Controllers\Api\StockAlertApiController::class);
Route::customResource('/return-refund', App\Http\Controllers\Api\ReturnRefundApiController::class);
Route::get('/address/{id}/delete', [App\Http\Controllers\Api\UserApiController::class, 'DeleteAddress'])->name('DeleteAddress');
Route::post('user-order-email', [App\Http\Controllers\Api\UserApiController::class, 'UserOrderSendEmail'])->name('UserOrderSendEmail');
Route::post('process-order-email', [App\Http\Controllers\Api\OrderApiController::class, 'ProcessEmail'])->name('ProcessEmail');
Route::post('delivered-order-check', [App\Http\Controllers\Api\OrderApiController::class, 'ProscsdcessEmail'])->name('ProscsdcessEmail');
Route::customResource('/email-jobs', App\Http\Controllers\Api\EmailJobsApiController::class);
Route::customResource('/product-faqs', App\Http\Controllers\Api\ProductFaqsApiController::class);
Route::customResource('/mobile-home-page', App\Http\Controllers\Api\MobileHomePageApiController::class);
Route::customResource('/project-sale', App\Http\Controllers\Api\ProjectSaleApiController::class);
Route::customResource('/mobile_setting', App\Http\Controllers\Api\MobileSettingApiController::class);
Route::customResource('/contact_us', App\Http\Controllers\Api\ContactUsApiController::class);
Route::customResource('/newslatter', App\Http\Controllers\Api\NewslatterController::class);
Route::post('/maintainance-disabled-pro', [App\Http\Controllers\Api\MaintenanceApiController::class, 'AddDisabledPro'])->name('api.AddDisabledPro');
Route::get('getmaintainancedisabledpros', [App\Http\Controllers\Api\MaintenanceApiController::class, 'getDisabledProductData'])->name('getDisabledProductData');
Route::customResource('/customer-segmentation', App\Http\Controllers\Api\CustomerSegmentationController::class);
Route::customResource('/input_channels', App\Http\Controllers\Api\InputChannelController::class);
Route::customResource('/company_tags', App\Http\Controllers\Api\CompanyTagsController::class);
Route::customResource('/company_types', App\Http\Controllers\Api\CompanyTypesController::class);
Route::customResource('/marketplace_sales', App\Http\Controllers\Api\MarketplaceSalesController::class);

// CSV Import
Route::post('csv/tag', [App\Http\Controllers\Api\CSVController::class, 'Tags'])->name('csv.Tags');
Route::post('csv/market-place', [App\Http\Controllers\Api\CSVController::class, 'MarketPlace'])->name('csv.MarketPlace');
Route::post('csv/brand', [App\Http\Controllers\Api\CSVController::class, 'Brands'])->name('csv.Brands');
Route::post('csv/category', [App\Http\Controllers\Api\CSVController::class, 'Categories'])->name('csv.Categories');
Route::post('csv/product', [App\Http\Controllers\Api\CSVController::class, 'Products'])->name('csv.Products');
Route::post('csv/product-keyfeatures', [App\Http\Controllers\Api\CSVController::class, 'ProductKeyFeatures'])->name('csv.ProductKeyFeatures');
Route::post('csv/product-specifications', [App\Http\Controllers\Api\CSVController::class, 'ProductSpecifications'])->name('csv.ProductSpecifications');
Route::post('csv/cities', [App\Http\Controllers\Api\CSVController::class, 'Cities'])->name('csv.Cities');
Route::post('csv/regions', [App\Http\Controllers\Api\CSVController::class, 'Regions'])->name('csv.Regions');
Route::post('csv/regional-module', [App\Http\Controllers\Api\CSVController::class, 'RegionalModule'])->name('csv.RegionalModule');
Route::post('csv/doorstep', [App\Http\Controllers\Api\CSVController::class, 'DoorStep'])->name('csv.DoorStep');
Route::post('csv/notifications', [App\Http\Controllers\Api\CSVController::class, 'Notifications'])->name('csv.Notifications');
Route::post('csv/sub-tag', [App\Http\Controllers\Api\CSVController::class, 'SubTags'])->name('csv.SubTags');
Route::post('csv/notify-product', [App\Http\Controllers\Api\CSVController::class, 'NotifyProduct'])->name('csv.NotifyProduct');
Route::post('csv/user', [App\Http\Controllers\Api\CSVController::class, 'Users'])->name('csv.Users');
Route::post('csv/price-alert', [App\Http\Controllers\Api\CSVController::class, 'PriceAlert'])->name('csv.PriceAlert');
Route::post('csv/product-review', [App\Http\Controllers\Api\CSVController::class, 'ProductReview'])->name('csv.ProductReview');
Route::post('csv/save-search', [App\Http\Controllers\Api\CSVController::class, 'SaveSearch'])->name('csv.SaveSearch');
Route::post('csv/stock-alert', [App\Http\Controllers\Api\CSVController::class, 'StockAlert'])->name('csv.StockAlert');
Route::post('csv/brand-landing', [App\Http\Controllers\Api\CSVController::class, 'BrandLandingPage'])->name('csv.BrandLandingPage');
Route::post('csv/brand-landing-categories', [App\Http\Controllers\Api\CSVController::class, 'BrandLandingArray'])->name('csv.BrandLandingArray');

// CSV Export
Route::post('tag-csv', [App\Http\Controllers\Api\ExportCsvController::class, 'TagExportCsv'])->name('TagExport');
Route::post('brand-csv', [App\Http\Controllers\Api\ExportCsvController::class, 'BrandExportCsv'])->name('BrandExport');
Route::post('product-export', [App\Http\Controllers\Api\ExportCsvController::class, 'ProductExport'])->name('ProductExport');
Route::post('category-csv', [App\Http\Controllers\Api\ExportCsvController::class, 'CategoryExportCsv'])->name('CategoryExport');
Route::post('cities-csv', [App\Http\Controllers\Api\ExportCsvController::class, 'exportCitiesCsv'])->name('api.citiescsv');
Route::post('region-csv', [App\Http\Controllers\Api\ExportCsvController::class, 'exportRegionCsv'])->name('api.regioncsv');
Route::post('regional-csv', [App\Http\Controllers\Api\ExportCsvController::class, 'exportRegionalCsv'])->name('api.regionalcsv');
Route::post('door-step-csv', [App\Http\Controllers\Api\ExportCsvController::class, 'exportDoorStepCsv'])->name('api.doorstepcsv');
Route::post('notification-csv', [App\Http\Controllers\Api\ExportCsvController::class, 'exportNotificationCsv'])->name('api.notificationcsv');
Route::post('sub-tags-csv', [App\Http\Controllers\Api\ExportCsvController::class, 'exportSubTagCsv'])->name('api.subtagscsv');
Route::post('notify-product-csv', [App\Http\Controllers\Api\ExportCsvController::class, 'exportNotifyProductCsv'])->name('api.notifyproductcsv');
Route::post('user-csv', [App\Http\Controllers\Api\ExportCsvController::class, 'UserExportCsv'])->name('api.UserExport');
Route::post('product-csv', [App\Http\Controllers\Api\ExportCsvController::class, 'ProductExportCsv'])->name('api.ProductExportCsv');
Route::post('market-sales-csv', [App\Http\Controllers\Api\ExportCsvController::class, 'MarketSalesCsv'])->name('api.MarketSalesCsv');
Route::post('market-sales-xlsx', [App\Http\Controllers\Api\ExportCsvController::class, 'MarketSalesXlsx'])->name('api.MarketSalesXlsx');
Route::post('productspecs-csv', [App\Http\Controllers\Api\ExportCsvController::class, 'exportProductSpecsCsv'])->name('api.ProductSpecsExportCsv');
Route::post('productfeatures-csv', [App\Http\Controllers\Api\ExportCsvController::class, 'exportProductFeaturesCsv'])->name('api.ProductFeaturesExportCsv');
Route::post('price-alert-csv', [App\Http\Controllers\Api\ExportCsvController::class, 'exportPriceAlertCsv'])->name('api.pricealertcsv');
Route::post('order-csv', [App\Http\Controllers\Api\ExportCsvController::class, 'exportOrderCSv'])->name('api.ordercsv');
Route::post('product-review-csv', [App\Http\Controllers\Api\ExportCsvController::class, 'exportProductReviewCsv'])->name('api.productreviewcsv');
Route::post('save-search-csv', [App\Http\Controllers\Api\ExportCsvController::class, 'exportSaveSearchCsv'])->name('api.savesearchcsv');
Route::post('producthistory-csv', [App\Http\Controllers\Api\ExportCsvController::class, 'exportProductHistoryCsv'])->name('api.exportProductHistoryCsv');
Route::post('stock-alert-csv', [App\Http\Controllers\Api\ExportCsvController::class, 'exportStockAlertCsv'])->name('api.exportStockAlertCsv');
Route::post('order-erp-csv', [App\Http\Controllers\Api\ExportCsvController::class, 'exportErpOrderCsv'])->name('api.exportErpOrderCsv');
Route::post('abandoned-cart-csv', [App\Http\Controllers\Api\ExportCsvController::class, 'exportAbandonedCartCsv'])->name('api.exportAbandonedCartCsv');

// Xlsx Export 
Route::post('tag-xlsx', [App\Http\Controllers\Api\ExportCsvController::class, 'exportTags'])->name('api.tagsxslx');
Route::post('brand-xlsx', [App\Http\Controllers\Api\ExportCsvController::class, 'exportBrands'])->name('api.brandsxslx');
Route::post('category-xlsx', [App\Http\Controllers\Api\ExportCsvController::class, 'exportCategories'])->name('api.categoriesxslx');
Route::post('product-xlsx', [App\Http\Controllers\Api\ExportCsvController::class, 'exportProducts'])->name('api.productsxslx');
Route::post('cities-xlsx', [App\Http\Controllers\Api\ExportCsvController::class, 'exportCities'])->name('api.citiesxslx');
Route::post('region-xlsx', [App\Http\Controllers\Api\ExportCsvController::class, 'exportRegion'])->name('api.regionxslx');
Route::post('productspecs-xlsx', [App\Http\Controllers\Api\ExportCsvController::class, 'exportProductSpecs'])->name('api.productspecsxslx');
Route::post('productfeatures-xlsx', [App\Http\Controllers\Api\ExportCsvController::class, 'exportProductFeatures'])->name('api.productfeaturesxslx');
Route::post('regional-xlsx', [App\Http\Controllers\Api\ExportCsvController::class, 'exportRegional'])->name('api.regionalxslx');
Route::post('door-step-xlsx', [App\Http\Controllers\Api\ExportCsvController::class, 'exportDoorStep'])->name('api.doorstepxslx');
Route::post('notification-xlsx', [App\Http\Controllers\Api\ExportCsvController::class, 'exportNotifications'])->name('api.notificationxslx');
Route::post('sub-tag-xlsx', [App\Http\Controllers\Api\ExportCsvController::class, 'exportSubTags'])->name('api.subtagsxslx');
Route::post('notify-product-xlsx', [App\Http\Controllers\Api\ExportCsvController::class, 'exportNotifyProduct'])->name('api.notifyproductxslx');
Route::post('user-xlsx', [App\Http\Controllers\Api\ExportCsvController::class, 'exportUsers'])->name('api.usersxslx');
Route::post('price-alert-xlsx', [App\Http\Controllers\Api\ExportCsvController::class, 'exportPriceAlert'])->name('api.pricealertxslx');
Route::post('order-xlsx', [App\Http\Controllers\Api\ExportCsvController::class, 'exportOrder'])->name('api.orderxslx');
Route::post('product-review-xlsx', [App\Http\Controllers\Api\ExportCsvController::class, 'exportProductReview'])->name('api.productreviewxslx');
Route::post('save-search-xlsx', [App\Http\Controllers\Api\ExportCsvController::class, 'exportSaveSearch'])->name('api.savesearchxslx');
Route::post('pim-analytics-product-xlsx/{date}', [App\Http\Controllers\Api\ExportCsvController::class, 'exportPimAnalyProducts'])->name('api.PimAnalyProductsxlsx');
Route::post('pim-analytics-brand-xlsx/{date}', [App\Http\Controllers\Api\ExportCsvController::class, 'exportPimAnalyBrands'])->name('api.PimAnalyBrandsxlsx');
Route::post('pim-analytics-category-xlsx/{date}', [App\Http\Controllers\Api\ExportCsvController::class, 'exportPimAnalyCategory'])->name('api.PimAnalyCategoryxlsx');
Route::post('sales-cities-xlsx/{date}', [App\Http\Controllers\Api\ExportCsvController::class, 'exportSalesCities'])->name('api.SalesCitiesxlsx');
Route::post('sales-report-export-product', [App\Http\Controllers\Api\ExportCsvController::class, 'salesReportExportProduct'])->name('salesReportExportProduct');
Route::post('sales-report-export-brand', [App\Http\Controllers\Api\ExportCsvController::class, 'salesReportExportBrand'])->name('salesReportExportBrand');
Route::post('sales-report-export-category', [App\Http\Controllers\Api\ExportCsvController::class, 'salesReportExportCategory'])->name('salesReportExportCategory');
Route::post('sales-report-export-pmethod', [App\Http\Controllers\Api\ExportCsvController::class, 'salesReportExportPMethod'])->name('salesReportExportPMethod');
Route::post('users-analysis-xlsx/{date}', [App\Http\Controllers\Api\ExportCsvController::class, 'exportUsersAnalysis'])->name('api.UsersAnalysisxlsx');
Route::post('producthistory-xlsx', [App\Http\Controllers\Api\ExportCsvController::class, 'exportProductHistory'])->name('api.exportProductHistory');
Route::post('stock-alert-xlsx', [App\Http\Controllers\Api\ExportCsvController::class, 'exportStockAlert'])->name('api.exportStockAlert');
Route::post('order-erp-xlsx', [App\Http\Controllers\Api\ExportCsvController::class, 'exportErpOrder'])->name('api.exportErpOrder');
Route::post('abandoned-cart-xlsx', [App\Http\Controllers\Api\ExportCsvController::class, 'exportAbandonedCart'])->name('api.exportAbandonedCart');

// Store Users
Route::get('/get-live-users', [App\Http\Controllers\Api\UserApiController::class, 'getLiveUsers'])->name('api.getLiveUsers');
Route::get('/get-live-orders', [App\Http\Controllers\Api\UserApiController::class, 'getLiveOrders'])->name('api.getLiveOrders');
Route::get('/get-live-product-reviews', [App\Http\Controllers\Api\UserApiController::class, 'getLiveProductReviews'])->name('api.getLiveProductReviews');
Route::get('/reset-request-check/{token}', [App\Http\Controllers\Api\UserApiController::class, 'checkToken'])->name('api.checkToken');
Route::get('/get-permission/{userid}', [App\Http\Controllers\Api\UserApiController::class, 'getPermission'])->name('api.getPermission');
Route::get('/order-waybill-update',[App\Http\Controllers\Api\UserApiController::class, 'OrderWaybillUpdate'])->name('OrderWaybillUpdate');

// Sales report
Route::get('/sales-report/{date}', [App\Http\Controllers\Api\SalesReportController::class, 'reportIndex'])->name('api.reportIndex');
// Pim Analytics
Route::get('/pim-report/{date}', [App\Http\Controllers\Api\SalesReportController::class, 'pimReport'])->name('api.pimReport');
// User Report 
Route::get('/user-report/{date}', [App\Http\Controllers\Api\UserReportApiController::class, 'Userreport'])->name('api.Userreport');
// store gift voucher api
Route::post('store-gv-sms', [App\Http\Controllers\Api\GiftVoucherApiController::class, 'storeGvSms'])->name('api.storeGvSms');
// store Wallet api
Route::post('store-wallet-sms', [App\Http\Controllers\Api\WalletApiController::class, 'storeWalletSms'])->name('api.storeWalletSms');
// store Voucher api
Route::post('store-coupon-sms', [App\Http\Controllers\Api\CouponApiController::class, 'storeCouponSms'])->name('api.storeCouponSms');
// Slider Setting
Route::get('/slider-setting', [App\Http\Controllers\Api\SliderApiController::class, 'settingdata'])->name('api.settingdata');
Route::post('setting-data', [App\Http\Controllers\Api\SliderApiController::class, 'storeSettingFields'])->name('api.storeSettingFields');

Route::get('order/downloadPDF/{id}',[App\Http\Controllers\Api\OrderApiController::class, 'downloadPDF'])->name('api.downloadpdfback');
Route::post('multi-pdf',[App\Http\Controllers\Api\OrderApiController::class, 'MultidownloadPDF'])->name('api.MultidownloadPDF');

// Apis 
Route::get('/updatelnorderstatusbyid/{id}/{data?}', [App\Http\Controllers\Api\ExtraApisController::class, 'UpdateLnOrderStatusById'])->name('api.UpdateLnOrderStatusById');
Route::get('/updatelninforbyid-onlydatetime/{id}/{data?}', [App\Http\Controllers\Api\ExtraApisController::class, 'UpdateLninforByIdOnlyDateTime'])->name('api.UpdateLninforByIdOnlyDateTime');
Route::get('/getdataunifonic/', [App\Http\Controllers\Api\ExtraApisController::class, 'getDataUnifonic'])->name('api.getDataUnifonic');
Route::get('/updateproductqtybysku/{id}/{data?}', [App\Http\Controllers\Api\ExtraApisController::class, 'UpdateProductQtyBySku'])->name('api.UpdateProductQtyBySku');
Route::get('/get-testing', [App\Http\Controllers\Api\ExtraApisController::class, 'getTesting'])->name('api.getTesting');
Route::get('/invoice-email', [App\Http\Controllers\Api\ExtraApisController::class, 'InvoiceEmail'])->name('api.InvoiceEmail');

Route::get('/getERP_orders', [App\Http\Controllers\Api\Frontend\GlobalController::class, 'getERPOrders'])->name('api.getERPOrders');
Route::get('/getcheckERP_orders', [App\Http\Controllers\Api\Frontend\GlobalController::class, 'getcheckERPOrders'])->name('api.getcheckERPOrders');
Route::get('/updatelninforbyid/{id}/{data?}', [App\Http\Controllers\Api\Frontend\GlobalController::class, 'UpdateLninforById'])->name('api.updatelninforbyid');
Route::get('/updatestatuslninforbyid/{id}/{data?}', [App\Http\Controllers\Api\Frontend\GlobalController::class, 'UpdateStatusLninforById'])->name('api.updatestatuslninforbyid');
// Route::get('/updatestatuslninforbyid/{id}/{data?}', [App\Http\Controllers\Api\Frontend\GlobalController::class, 'ProductFeedData'])->name('api.ProductFeedData');
Route::get('/criteo', [App\Http\Controllers\Api\Frontend\GlobalController::class, 'criteoProductFeedData'])->name('api.criteoProductFeedData');
Route::get('/socialmedia', [App\Http\Controllers\Api\Frontend\GlobalController::class, 'socialMediaProductFeedData'])->name('api.socialMediaProductFeedData');
Route::get('/couponstatus/{status}', [App\Http\Controllers\Api\Frontend\GlobalController::class, 'couponStatus'])->name('api.couponStatus');

Route::get('/orderexport', [App\Http\Controllers\Api\Frontend\GlobalController::class, 'orderExport'])->name('api.orderexport');
Route::get('/orderexportbyid', [App\Http\Controllers\Api\Frontend\GlobalController::class, 'orderExportbyId'])->name('api.orderexportbyid');
// Route::get('/frontend/slider-type/{type}', function($type) {
//     $seconds = 86400;
//     $sliders = Cache::remember('homesliders'.$type, $seconds, function () use ($type) {
//         return Slider::with('featuredImageWeb:id,image','featuredImageApp:id,image', 'cat:id,slug,name,name_arabic', 'pro:id,slug,sku,name,name_arabic', 'brand:id,slug,name,name_arabic')
//             ->orderBy('sorting', 'asc') 
//             ->where('position', $type)
//             ->where('status', 1)
//             ->select('id', 'name', 'name_ar', 'slider_type', 'alt', 'alt_ar', 'sorting'
//             ,'status', 'image_web','image_mobile', 'custom_link', 'redirection_type', 'product_id', 'brand_id', 'category_id','timer', 'position')->get();
//     });
//     $response = [
//         'data'=>$sliders,
//     ];
//     $responsejson=json_encode($response);
//     $data=gzencode($responsejson,9);
//     return response($data)->withHeaders([
//         'Content-type' => 'application/json; charset=utf-8',
//         'Content-Length'=> strlen($data),
//         'Content-Encoding' => 'gzip'
//     ]);
// })->name('api.getSlidersType');
// Route::group(['prefix' => 'frontend'], function () {
//     Route::get('/slider-type/{type}', [App\Http\Controllers\Api\Frontend\SliderController::class, 'getSlidersType'])->name('api.getSlidersType');
// });
// Frontend API's
Route::group(['prefix' => 'frontend'], function () {
    
    Route::get('/homepagelatest-one', [App\Http\Controllers\Api\Frontend\HomePageLatestController::class, 'getHomePagelatestOne'])->name('api.getHomePagelatestOne');
    Route::get('/homepagelatest-two', [App\Http\Controllers\Api\Frontend\HomePageLatestController::class, 'getHomePagelatestTwo'])->name('api.getHomePagelatestTwo');
    Route::get('/homepagelatest-three', [App\Http\Controllers\Api\Frontend\HomePageLatestController::class, 'getHomePagelatestThree'])->name('api.getHomePagelatestThree');
    Route::get('/getlatestcategoryproducts/{type}/{rowId}', [App\Http\Controllers\Api\Frontend\HomePageLatestController::class, 'getLatestCategoryProducts'])->name('api.getLatestCategoryProducts');
    
    Route::get('/marketing/surveyform/{id}', [App\Http\Controllers\Api\Frontend\SurveyFormController::class,'showFront']);
    Route::get('/get-ugc-data', [App\Http\Controllers\Api\Frontend\UserGeneratedContentApiController::class,'getUgcData']);
    Route::get('/marketing/ugc', [App\Http\Controllers\Api\Frontend\UserGeneratedContentApiController::class,'index']);
    Route::post('/marketing/ugc-store', [App\Http\Controllers\Api\Frontend\UserGeneratedContentApiController::class,'store']);
    Route::post('/survey-responses', [App\Http\Controllers\Api\Frontend\SurveyFormController::class, 'store']);
    Route::get('/shipment-tracking-detail/{id}', [App\Http\Controllers\Api\Frontend\ShipmentTrackingController::class, 'ShipmentTrackingDetail'])->name('api.ShipmentTrackingDetail');
    //Browser
    Route::get('/get-browser', [App\Http\Controllers\Api\Frontend\TermsAndConditionsController::class, 'storeUserBrowser'])->name('api.getBrowser');
    Route::get('/browser-stats', [App\Http\Controllers\Api\Frontend\TermsAndConditionsController::class, 'getBrowserStats'])->name('api.browserStats');
    //matintenance locater
    Route::get('/get-maintenance-locater', [App\Http\Controllers\Api\Frontend\MaintenanceController::class, 'getMaintenanceLocater'])->name('api.getMaintenanceLocater');
    Route::post('/filter-maintenance', [App\Http\Controllers\Api\Frontend\MaintenanceController::class, 'FilterMaitenance'])->name('api.FilterMaitenance');
    
    //ticket-frontend
    Route::get('/getallbrands', [App\Http\Controllers\Api\Frontend\TicketController::class, 'getAllBrands'])->name('api.getAllBrands');
    Route::post('/ticket-usercheck', [App\Http\Controllers\Api\Frontend\TicketController::class, 'ticketUserCheck'])->name('api.ticketUserCheck');
    Route::post('/internal-ticket-save', [App\Http\Controllers\Api\Frontend\TicketController::class, 'storeTicket'])->name('api.storeTicket');
    Route::get('/get-internal-ticket', [App\Http\Controllers\Api\Frontend\TicketController::class, 'ticketIndex'])->name('api.ticketIndex');
    //terms and conditions
    Route::get('/terms-and-conditions', [App\Http\Controllers\Api\Frontend\TermsAndConditionsController::class, 'index'])->name('api.terms_index');
    // Route::get('/getCouponAmounts/{userid}', [App\Http\Controllers\Api\Frontend\GlobalController::class, 'index'])->name('api.terms_index');
    //shipment tracking
    Route::get('/shipment-tracking/{slug}', [App\Http\Controllers\Api\Frontend\ShipmentTrackingController::class, 'shipmentTracking'])->name('api.shipmentTrackingPage');
    //shipment tracking location
    Route::post('/shipment-tracking/location/{id}', [App\Http\Controllers\Api\Frontend\ShipmentTrackingController::class, 'shipmentTrackingLocation'])->name('api.shipmentTrackingLocation');
    
    Route::get('/menu', [App\Http\Controllers\Api\Frontend\GlobalController::class, 'Menu'])->name('api.Menu');
    Route::get('/brands/{slug}', [App\Http\Controllers\Api\Frontend\BrandController::class, 'BrandPage'])->name('api.BrandPage');
    
    
    Route::get('searchpage', [App\Http\Controllers\Api\Frontend\CategoryController::class, 'searchpage'])->name('api.searchpage');
    Route::get('searchpage-regional', [App\Http\Controllers\Api\Frontend\CategoryController::class, 'searchpageRegional'])->name('api.searchpageRegional');
    
    Route::get('searchpage-regional-new/{city}', [App\Http\Controllers\Api\Frontend\CategoryController::class, 'searchpageRegionalNew'])->name('api.searchpageRegionalNew');
    
    Route::get('newly-arrived', [App\Http\Controllers\Api\Frontend\CategoryController::class, 'NewlyArrived'])->name('api.NewlyArrived');
    Route::get('newly-arrived-regional', [App\Http\Controllers\Api\Frontend\CategoryController::class, 'NewlyArrivedRegional'])->name('api.NewlyArrivedRegional');
    
    Route::get('newly-arrived-regional-new/{city}', [App\Http\Controllers\Api\Frontend\CategoryController::class, 'NewlyArrivedRegionalNew'])->name('api.NewlyArrivedRegionalNew');
    
    Route::get('most-rated', [App\Http\Controllers\Api\Frontend\CategoryController::class, 'MostRated'])->name('api.MostRated');
    Route::get('most-rated-regional', [App\Http\Controllers\Api\Frontend\CategoryController::class, 'MostRatedRegional'])->name('api.MostRatedRegional');
    
     Route::get('most-rated-regional-new/{city}', [App\Http\Controllers\Api\Frontend\CategoryController::class, 'MostRatedRegionalNew'])->name('api.MostRatedRegionalNew');
    
    Route::get('category-sliders/{id}', [App\Http\Controllers\Api\Frontend\CategoryController::class, 'CatSliders'])->name('api.CatSliders');
    Route::get('category-products/{slug}', [App\Http\Controllers\Api\Frontend\CategoryController::class, 'CatProducts'])->name('api.CatProducts');
    Route::get('category-products-regional/{slug}', [App\Http\Controllers\Api\Frontend\CategoryController::class, 'CatProductsRegional'])->name('api.CatProductsRegional');
    //filter
    Route::get('test-filter-brandCategory/{brandslug?}/{slug?}/{city?}', [App\Http\Controllers\Api\Frontend\CategoryController::class, 'TestFilterBrandCategory'])->name('api.TestFilterBrandCategory');
    //
    
    Route::get('category-products-regional-new/{slug}/{city}', [App\Http\Controllers\Api\Frontend\CategoryController::class, 'CatProductsRegionalNew'])->name('api.CatProductsRegionalNew');
    Route::get('category-products-regional-new-testing/{slug}/{city}', [App\Http\Controllers\Api\Frontend\CategoryController::class, 'CatProductsRegionalNewTesting'])->name('api.CatProductsRegionalNewTesting');
    
    Route::get('/product/{slug}/{city?}', [App\Http\Controllers\Api\Frontend\ProductController::class, 'ProductDetailPage'])->name('api.ProductDetailPage');
    Route::get('/product-regional/{slug}/{city?}', [App\Http\Controllers\Api\Frontend\ProductController::class, 'ProductDetailPageRegional'])->name('api.ProductDetailPageRegional');
    
    Route::get('/product-regional-new/{slug}/{city?}', [App\Http\Controllers\Api\Frontend\ProductController::class, 'ProductDetailPageRegionalNew'])->name('api.ProductDetailPageRegionalNew');
    Route::get('/product-regional-new-testing/{slug}/{city?}', [App\Http\Controllers\Api\Frontend\ProductController::class, 'ProductDetailPageRegionalNewCopy'])->name('api.ProductDetailPageRegionalNewCopy');
    Route::get('/product-regional-new-copy/{slug}/{city?}', [App\Http\Controllers\Api\Frontend\ProductController::class, 'ProductDetailPageRegionalNewTesting'])->name('api.ProductDetailPageRegionalNewTesting');
    
    Route::get('/productextradata/{id}/{city?}', [App\Http\Controllers\Api\Frontend\ProductController::class, 'ProductDetailPageExtraData'])->name('api.ProductDetailPageExtraData');
    Route::get('/productextradata-cart-popup/{id}/{city?}/{userid?}', [App\Http\Controllers\Api\Frontend\ProductController::class, 'ProductDetailPageExtraDataCartPopup'])->name('api.ProductDetailPageExtraDataCartPopup');
    Route::get('/productextradatamulti/{ids}/{city?}', [App\Http\Controllers\Api\Frontend\ProductController::class, 'ProductExtraDatamulti'])->name('api.ProductExtraDatamulti');
    Route::get('/footer_pages/{slug}', [App\Http\Controllers\Api\Frontend\FooterPagesController::class, 'FooterPage'])->name('api.FooterPage');
    Route::get('/homepage-frontend', [App\Http\Controllers\Api\Frontend\HomePageController::class, 'HomePageFrontend'])->name('api.HomePageFrontend');
    Route::get('/getfreegift/{id}', [App\Http\Controllers\Api\Frontend\GlobalController::class, 'getFreeGift'])->name('api.getFreeGift');
    Route::post('getfreegift-cart', [App\Http\Controllers\Api\Frontend\GlobalController::class, 'getFreeGiftCart'])->name('api.getFreeGiftCart');
    Route::get('/getfbt/{id}', [App\Http\Controllers\Api\Frontend\GlobalController::class, 'getFBT'])->name('api.getFBT');
    Route::get('/getexpressdelivery/{id}', [App\Http\Controllers\Api\Frontend\GlobalController::class, 'getExpressDelivery'])->name('api.getExpressDelivery');
    Route::get('/getbadge/{id}', [App\Http\Controllers\Api\Frontend\GlobalController::class, 'getBadge'])->name('api.getBadge');
    Route::post('/getlogin', [App\Http\Controllers\Api\Frontend\UserController::class, 'getLogin'])->name('api.getLogin');
    Route::post('/getregister', [App\Http\Controllers\Api\Frontend\UserController::class, 'getRegister'])->name('api.getRegister');
    Route::get('/getwishlist/{id}', [App\Http\Controllers\Api\Frontend\WishlistController::class, 'getWishlist'])->name('api.getWishlist');
    Route::get('/getwishlist-regional/{id}', [App\Http\Controllers\Api\Frontend\WishlistController::class, 'getWishlistRegional'])->name('api.getWishlistRegional');
    
    Route::get('/getwishlist-regional-new/{id}/{city}', [App\Http\Controllers\Api\Frontend\WishlistController::class, 'getWishlistRegionalNew'])->name('api.getWishlistRegionalNew');
    
    
    Route::get('/getwishlistproduct/{id}', [App\Http\Controllers\Api\Frontend\WishlistController::class, 'getWishlistProduct'])->name('api.getWishlistProduct');
    
    Route::post('/checkwishlistproduct', [App\Http\Controllers\Api\Frontend\WishlistController::class, 'checkWishlistProduct'])->name('api.checkWishlistProduct');
    Route::get('/checkmultiwishlistproduct/{productids}/{userid}', [App\Http\Controllers\Api\Frontend\WishlistController::class, 'checkMultiWishlistProduct'])->name('api.checkMultiWishlistProduct');
    Route::post('/addwishlist', [App\Http\Controllers\Api\Frontend\WishlistController::class, 'addWishlist'])->name('api.addWishlist');
    Route::post('/removewishlist', [App\Http\Controllers\Api\Frontend\WishlistController::class, 'removeWishlist'])->name('api.removeWishlist');
    Route::post('/getshipping', [App\Http\Controllers\Api\Frontend\ShippingController::class, 'getShippingUpdate'])->name('api.getShippingUpdate');
    Route::post('/getshippingupdate', [App\Http\Controllers\Api\Frontend\ShippingController::class, 'getShipping'])->name('api.getShipping');
    Route::get('/blogs', [App\Http\Controllers\Api\Frontend\BlogController::class, 'BlogListing'])->name('api.BlogListing');
    Route::get('/blog/{slug}', [App\Http\Controllers\Api\Frontend\BlogController::class, 'BlogDetail'])->name('api.BlogDetail');
    Route::get('/user/{id}/{device?}', [App\Http\Controllers\Api\Frontend\UserController::class, 'getUserData'])->name('api.getUserData');
    Route::get('/userwallet/{id}/{device?}', [App\Http\Controllers\Api\Frontend\UserController::class, 'getUserWallet'])->name('api.getUserWallet');
    Route::post('/discountRule/{device?}', [App\Http\Controllers\Api\Frontend\DiscountRuleController::class, 'getDiscountRule'])->name('api.getDiscountRule');
    Route::post('/couponData/{device?}', [App\Http\Controllers\Api\Frontend\CouponController::class, 'getCoupon'])->name('api.getCouponData');
    Route::get('/giftVoucherData/{id}/{device?}', [App\Http\Controllers\Api\Frontend\GiftVoucherApiController::class, 'getGiftVoucher'])->name('api.getGiftVoucher');
    Route::get('/walletData/{id}/{device?}', [App\Http\Controllers\Api\Frontend\WalletDataApiController::class, 'getWalletData'])->name('api.getWalletData');
    Route::post('/walletDataCheckout', [App\Http\Controllers\Api\Frontend\WalletDataApiController::class, 'getWalletDataCheckout'])->name('api.getWalletDataCheckout');
    Route::get('/affiliationData/{id}', [App\Http\Controllers\Api\Frontend\AffiliationApiController::class, 'getAffiliation'])->name('api.getAffiliation');
    Route::get('/getloyaltyPointsData/{id}/{device?}', [App\Http\Controllers\Api\Frontend\LoyaltyPointsApiController::class, 'getloyaltyPoints'])->name('api.getloyaltyPoints');
    Route::get('/user-orders/{id}', [App\Http\Controllers\Api\Frontend\UserController::class, 'getUserOrderData'])->name('api.getUserOrderData');
    Route::get('/user-deliveredorders/{id}', [App\Http\Controllers\Api\Frontend\UserController::class, 'getUserDeliveredOrderData'])->name('api.getUserDeliveredOrderData');
    Route::get('/user-addresses/{id}', [App\Http\Controllers\Api\Frontend\UserController::class, 'getUserAddressData'])->name('api.getUserAddressData');
    Route::get('/getflashsale', [App\Http\Controllers\Api\Frontend\GlobalController::class, 'getFlashSale'])->name('api.getFlashSale');
    Route::get('/order-detail/{id}', [App\Http\Controllers\Api\Frontend\UserController::class, 'getOrderData'])->name('api.getOrderData');
    Route::get('/orderdata-thankyou/{id}', [App\Http\Controllers\Api\Frontend\UserController::class, 'getOrderDataThankYou'])->name('api.getOrderDataThankYou');
    Route::get('/checkorder-review/{id}/{userid}', [App\Http\Controllers\Api\Frontend\UserController::class, 'getCheckOrderReview'])->name('api.getCheckOrderReview');
    Route::get('/checkmaintenance-product/{id}/', [App\Http\Controllers\Api\Frontend\UserController::class, 'getCheckMaintenanceProduct'])->name('api.getCheckMaintenanceProduct');
    Route::get('/getcompanytypes', [App\Http\Controllers\Api\Frontend\UserController::class, 'getCompanyTypes'])->name('api.getCompanyTypes');
    Route::post('/createecommerceticket', [App\Http\Controllers\Api\Frontend\UserController::class, 'createEcommerceTicket'])->name('api.createEcommerceTicket');
    Route::post('/addproductreview', [App\Http\Controllers\Api\Frontend\UserController::class, 'addProductReview'])->name('addProductReview');
    Route::get('/search', [App\Http\Controllers\Api\Frontend\SearchController::class, 'search'])->name('api.search');
    Route::get('/search-regional', [App\Http\Controllers\Api\Frontend\SearchController::class, 'searchRegional'])->name('api.searchRegional');
    
    Route::get('/search-regional-new', [App\Http\Controllers\Api\Frontend\SearchController::class, 'searchRegionalNew'])->name('api.searchRegionalNew');
    Route::get('/search-regional-new-updated', [App\Http\Controllers\Api\Frontend\SearchController::class, 'searchRegionalNewUpdated'])->name('api.searchRegionalNewUpdated');
    
    Route::post('/checkcompareproduct', [App\Http\Controllers\Api\Frontend\CompareController::class, 'checkCompareProduct'])->name('api.checkCompareProduct');
    Route::post('/addcompare', [App\Http\Controllers\Api\Frontend\CompareController::class, 'addCompare'])->name('api.addCompare');
    Route::post('/removecompare', [App\Http\Controllers\Api\Frontend\CompareController::class, 'removeCompare'])->name('api.removeCompare');
    Route::post('/removeallcompare', [App\Http\Controllers\Api\Frontend\CompareController::class, 'removeAllCompare'])->name('api.removeAllCompare');
    Route::get('/getcompare/{id}', [App\Http\Controllers\Api\Frontend\CompareController::class, 'getCompare'])->name('api.getCompare');
    Route::get('/homepagepartone-regional', [App\Http\Controllers\Api\Frontend\HomePageController::class, 'HomePagePartOneRegional'])->name('api.HomePagePartOneRegional');
    //clone
    Route::get('/homepagepartone-regional-copy', [App\Http\Controllers\Api\Frontend\HomePageController::class, 'HomePagePartOneRegionalCopy'])->name('api.HomePagePartOneRegionalCopy');
    Route::get('/homepageparttwo-regional-copy', [App\Http\Controllers\Api\Frontend\HomePageController::class, 'HomePagePartTwoRegionalCopy'])->name('api.HomePagePartTwoRegionalCopy');
    Route::get('/homepagepartthree-regional-copy', [App\Http\Controllers\Api\Frontend\HomePageController::class, 'HomePagePartThreeRegionalCopy'])->name('api.HomePagePartThreeRegionalCopy');
    Route::get('/homepage-frontend-copy', [App\Http\Controllers\Api\Frontend\HomePageController::class, 'HomePageFrontendCopy'])->name('api.HomePageFrontendCopy');
    //
    
     Route::get('/homepagepartone-regional-new/{city}', [App\Http\Controllers\Api\Frontend\HomePageController::class, 'HomePagePartOneRegionalNew'])->name('api.HomePagePartOneRegionalNew');
    
    Route::get('/homepageparttwo-regional', [App\Http\Controllers\Api\Frontend\HomePageController::class, 'HomePagePartTwoRegional'])->name('api.HomePagePartTwoRegional');
    
    Route::get('/homepageparttwo-regional-new/{city}', [App\Http\Controllers\Api\Frontend\HomePageController::class, 'HomePagePartTwoRegionalNew'])->name('api.HomePagePartTwoRegionalNew');
    
    Route::get('/homepagepartthree-regional', [App\Http\Controllers\Api\Frontend\HomePageController::class, 'HomePagePartThreeRegional'])->name('api.HomePagePartThreeRegional');
    
    Route::get('/homepagepartthree-regional-new/{city}', [App\Http\Controllers\Api\Frontend\HomePageController::class, 'HomePagePartThreeRegionalNew'])->name('api.HomePagePartThreeRegionalNew');
    
    
    Route::get('/productextradata-cart-popup-regional/{id}/{city?}/{userid?}', [App\Http\Controllers\Api\Frontend\ProductController::class, 'ProductDetailPageExtraDataCartPopuRegional'])->name('api.ProductDetailPageExtraDataCartPopuRegional');
    
    Route::get('/productextradata-cart-popup-regional-new/{id}/{city?}/{userid?}', [App\Http\Controllers\Api\Frontend\ProductController::class, 'ProductDetailPageExtraDataCartPopuRegionalNew'])->name('api.ProductDetailPageExtraDataCartPopuRegionalNew');
    
    Route::get('/productextradata-regional/{id}/{city?}', [App\Http\Controllers\Api\Frontend\ProductController::class, 'ProductDetailPageExtraDataRegional'])->name('api.ProductDetailPageExtraDataRegional');
    
     Route::get('/productextradata-regional-new/{id}/{city?}', [App\Http\Controllers\Api\Frontend\ProductController::class, 'ProductDetailPageExtraDataRegionalNew'])->name('api.ProductDetailPageExtraDataRegionalNew');
    
    Route::post('/productextradata-regional-cart', [App\Http\Controllers\Api\Frontend\ProductController::class, 'ProductDetailPageExtraDataRegionalCart'])->name('api.ProductDetailPageExtraDataRegionalCart');
    
    Route::post('/productextradata-regional-new-cart/{city}', [App\Http\Controllers\Api\Frontend\ProductController::class, 'ProductDetailPageExtraDataRegionalCartNew'])->name('api.ProductDetailPageExtraDataRegionalCartNew');
    
    Route::get('/productextradatamulti-regional/{ids}/{city?}', [App\Http\Controllers\Api\Frontend\ProductController::class, 'ProductExtraDatamultiRegional'])->name('api.ProductExtraDatamultiRegional');
    
    Route::get('/productextradatamulti-regional-new/{ids}/{city?}', [App\Http\Controllers\Api\Frontend\ProductController::class, 'ProductExtraDatamultiRegionalNewUpdated'])->name('api.ProductExtraDatamultiRegionalNewUpdated');
    
    
    Route::get('/getcompareproduct/{id}', [App\Http\Controllers\Api\Frontend\CompareController::class, 'getCompareProduct'])->name('api.getCompareProduct');
    
    Route::get('/checkmulticompareproduct/{productids}/{userid}', [App\Http\Controllers\Api\Frontend\CompareController::class, 'checkMultiCompareProduct'])->name('api.checkMultiCompareProduct');
    Route::get('/user-profile/{id}', [App\Http\Controllers\Api\Frontend\UserController::class, 'getUserProfileData'])->name('api.getUserProfileData');
    Route::get('/user-address/{id}', [App\Http\Controllers\Api\Frontend\UserController::class, 'getAddressData'])->name('api.getAddressData');
    Route::get('/user-address-delete/{id}', [App\Http\Controllers\Api\Frontend\UserController::class, 'DeleteAddress'])->name('api.DeleteAddress');
    Route::get('/address-region-cities/{id}', [App\Http\Controllers\Api\Frontend\UserController::class, 'RegionCities'])->name('api.RegionCities');
    Route::get('/address-region-cities-new/{id}', [App\Http\Controllers\Api\Frontend\UserController::class, 'RegionCitiesNew'])->name('api.RegionCitiesNew');
    Route::get('/getcities/{lang}', [App\Http\Controllers\Api\Frontend\UserController::class, 'getCities'])->name('api.getCities');
    Route::get('/only-city/{city_name}', [App\Http\Controllers\Api\Frontend\UserController::class, 'onlyCity'])->name('api.onlyCity');
    Route::post('/addaddress', [App\Http\Controllers\Api\Frontend\UserController::class, 'CreateAddress'])->name('api.CreateAddress');
    Route::post('/updateaddress/{id}', [App\Http\Controllers\Api\Frontend\UserController::class, 'UpdateAddress'])->name('api.UpdateAddress');
    Route::post('/updateuser', [App\Http\Controllers\Api\Frontend\UserController::class, 'updateuser'])->name('updateuser');
    Route::get('/getstorelocator', [App\Http\Controllers\Api\Frontend\StoreLocatorController::class, 'getStoreLocators'])->name('api.getStoreLocators');
    Route::get('/getstorelocator-new', [App\Http\Controllers\Api\Frontend\StoreLocatorController::class, 'getStoreLocatorsNew'])->name('api.getStoreLocatorsNew');
    Route::get('/getstorelocator-new-update', [App\Http\Controllers\Api\Frontend\StoreLocatorController::class, 'getStoreLocatorsNewUpdate'])->name('api.getStoreLocatorsNewUpdate');
    Route::get('/getstorelocator-by-lncode', [App\Http\Controllers\Api\Frontend\CustomerSurveyController::class, 'getStoreLocaterDataByLnCode'])->name('api.getStoreLocaterDataByLnCode');
    Route::post('/submit-customer-survey-form', [App\Http\Controllers\Api\Frontend\CustomerSurveyController::class, 'submitCustomerSurveyForm'])->name('api.submitCustomerSurveyForm');
    Route::post('/checkpaymentmethod', [App\Http\Controllers\Api\Frontend\CheckoutController::class, 'checkPaymentMethod'])->name('api.checkPaymentMethod');
    Route::post('/addsave-search', [App\Http\Controllers\Api\Frontend\SearchController::class, 'CreateSaveSearch'])->name('api.CreateSaveSearch');
    Route::get('/getregions', [App\Http\Controllers\Api\Frontend\UserController::class, 'getRegData'])->name('api.getRegData');
    Route::post('/user-login', [App\Http\Controllers\Api\Frontend\UserController::class, 'CheckUserPhone'])->name('api.CheckUserPhone');
    Route::post('/user-img', [App\Http\Controllers\Api\Frontend\UserController::class, 'userImgUpload'])->name('api.userImgUpload');
    Route::post('/check-otp', [App\Http\Controllers\Api\Frontend\UserController::class, 'otpCheck'])->name('api.otpCheck');
    Route::post('/user-register', [App\Http\Controllers\Api\Frontend\UserController::class, 'RegisterUser'])->name('api.RegisterUser');
    Route::post('/register-check-phone', [App\Http\Controllers\Api\Frontend\UserController::class, 'RegisterPhoneCheck'])->name('api.RegisterPhoneCheck');
    Route::post('/check-register-otp', [App\Http\Controllers\Api\Frontend\UserController::class, 'otpRegisterCheck'])->name('api.otpRegisterCheck');
    Route::get('/sliders-data', [App\Http\Controllers\Api\Frontend\SliderController::class, 'getSliders'])->name('api.getSliders');
    Route::get('/slider-type/{type}', [App\Http\Controllers\Api\Frontend\SliderController::class, 'getSlidersType'])->name('api.getSlidersType');
    Route::post('/resend-otp', [App\Http\Controllers\Api\Frontend\UserController::class, 'ResendOtp'])->name('api.ResendOtp');
    Route::post('/addpricealert', [App\Http\Controllers\Api\Frontend\PriceAlertController::class, 'addPriceAlert'])->name('api.addPriceAlert');
    Route::post('/removepricealert', [App\Http\Controllers\Api\Frontend\PriceAlertController::class, 'removePriceAlert'])->name('api.removePriceAlert');
    Route::post('/checkpricealertproduct', [App\Http\Controllers\Api\Frontend\PriceAlertController::class, 'checkPriceAlertProduct'])->name('api.checkPriceAlertProduct');
    Route::post('/submitOrder', [App\Http\Controllers\Api\Frontend\OrderController::class, 'submitOrder'])->name('api.submitOrder');
    Route::post('/submitOrderDuplicate', [App\Http\Controllers\Api\Frontend\OrderController::class, 'submitOrderDuplicate'])->name('api.submitOrderDuplicate');
    Route::post('/giftCardStoreData', [App\Http\Controllers\Api\Frontend\OrderController::class, 'giftCardStoreData'])->name('api.giftCardStoreData');
    Route::post('/filter-stores', [App\Http\Controllers\Api\Frontend\StoreLocatorController::class, 'FilterStores'])->name('api.FilterStores');
    Route::post('/getfees', [App\Http\Controllers\Api\Frontend\FeesesController::class, 'getFees'])->name('api.getFees');
    Route::post('/getexpress', [App\Http\Controllers\Api\Frontend\FeesesController::class, 'getExpress'])->name('api.getExpress');
    Route::post('/getexpress-regional', [App\Http\Controllers\Api\Frontend\FeesesController::class, 'getExpressRegional'])->name('api.getExpressRegional');
    
    Route::post('/getexpress-regional-new', [App\Http\Controllers\Api\Frontend\FeesesController::class, 'getExpressRegionalNew'])->name('api.getExpressRegionalNew');
    
    Route::post('/getdoorstep', [App\Http\Controllers\Api\Frontend\FeesesController::class, 'getDoorStep'])->name('api.getDoorStep');
    Route::post('/maintenance', [App\Http\Controllers\Api\Frontend\FeesesController::class, 'Maintenance'])->name('api.Maintenance');
    Route::get('/order-paymentupdate/{orderid}/{paymentid}', [App\Http\Controllers\Api\Frontend\OrderController::class, 'updatepayment'])->name('api.updatepayment');
    Route::get('/order-paymentupdate-test/{orderid}', [App\Http\Controllers\Api\Frontend\OrderController::class, 'updatepaymentTest'])->name('api.updatepaymentTest');
    Route::get('/order-paymentupdate-test-new/{orderid}', [App\Http\Controllers\Api\Frontend\OrderController::class, 'updatepaymentTestNew'])->name('api.updatepaymentTestNew');
    
    Route::get('/gift-paymentupdate/{orderid}/{paymentid}', [App\Http\Controllers\Api\Frontend\OrderController::class, 'giftupdatepayment'])->name('api.giftupdatepayment');
    
    
    Route::get('/hyperpay/{orderid}/{lang}', [App\Http\Controllers\Api\Frontend\OrderController::class, 'hyperpay'])->name('api.hyperpay');
    
    Route::get('/gift-detail/{orderid}', [App\Http\Controllers\Api\Frontend\OrderController::class, 'giftdetail'])->name('api.giftdetail');
    
    Route::get('/hyperpaygift/{orderid}/{lang}', [App\Http\Controllers\Api\Frontend\OrderController::class, 'hyperpaygift'])->name('api.hyperpaygift');
    
    
    Route::get('/checkaffiliation/{slug}', [App\Http\Controllers\Api\Frontend\AffiliationApiController::class, 'getCheckAffiliation'])->name('api.CheckAffiliation');
    Route::post('/addstockalert', [App\Http\Controllers\Api\Frontend\StockAlertController::class, 'addStockAlert'])->name('api.addStockAlert');
    Route::post('/removestockalert', [App\Http\Controllers\Api\Frontend\StockAlertController::class, 'removeStockAlert'])->name('api.v');
    Route::post('/checkstockalertproduct', [App\Http\Controllers\Api\Frontend\StockAlertController::class, 'checkStockAlertProduct'])->name('api.checkStockAlertProduct');
    Route::get('/user-returns/{id}', [App\Http\Controllers\Api\Frontend\ReturnRefundController::class, 'getUserReturns'])->name('api.getUserReturns');
    
    Route::get('/user-return-orders/{id}', [App\Http\Controllers\Api\Frontend\ReturnRefundController::class, 'getUserReturnOrders'])->name('api.getUserReturnOrders');
    
    
    Route::post('/addreturnrequest', [App\Http\Controllers\Api\Frontend\ReturnRefundController::class, 'CreateReturn'])->name('api.CreateReturn');
    Route::get('/return-questions', [App\Http\Controllers\Api\Frontend\ReturnRefundController::class, 'getReturnQuestions'])->name('api.getReturnQuestions');
    
    Route::get('/testdetails', [App\Http\Controllers\Api\Frontend\OrderController::class, 'testdetails'])->name('api.testdetails');
    
    Route::get('/downloadPDF/{id}',[App\Http\Controllers\Api\Frontend\OrderController::class, 'downloadPDF'])->name('api.downloadPDF');
    Route::get('/get-brands', [App\Http\Controllers\Api\Frontend\BrandController::class, 'getBrandsData'])->name('api.getBrandsData');
    Route::get('/notification-token/{token}/{device}/{userid?}', [App\Http\Controllers\Api\Frontend\NotificationApiController::class, 'notificationToken'])->name('api.notificationToken');
    Route::get('/send-notifi', [App\Http\Controllers\Api\Frontend\NotificationApiController::class, 'sendNotification'])->name('api.sendNotification');
    Route::post('/abandoned-cart-store', [App\Http\Controllers\Api\Frontend\AbandonedCartController::class, 'abandonedCartStore'])->name('api.abandonedCartStore');
    Route::get('/abandoned-cart-get/{slug}', [App\Http\Controllers\Api\Frontend\AbandonedCartController::class, 'abandonedCartGet'])->name('api.abandonedCartGet');
    Route::get('/app-sliders/', [App\Http\Controllers\Api\Frontend\AppSlidersController::class, 'appSliders'])->name('api.appSliders');
    Route::post('/store-contact-us', [App\Http\Controllers\Api\Frontend\ContactUsController::class, 'StoreContactUs'])->name('api.StoreContactUs');
    
    // For Mobile App
    Route::get('category-products-mob/{slug}', [App\Http\Controllers\Api\Frontend\Mobile\CategoryController::class, 'CatProductsMobile'])->name('api.CatProductsMobile');
    Route::get('/product-mob/{slug}', [App\Http\Controllers\Api\Frontend\Mobile\ProductController::class, 'ProductDetailPageMobile'])->name('api.ProductDetailPageMobile');
    Route::get('/product-mob-regional/{slug}', [App\Http\Controllers\Api\Frontend\Mobile\ProductController::class, 'ProductDetailPageMobileRegional'])->name('api.ProductDetailPageMobileRegional');
    
    Route::get('/product-mob-regional-new/{slug}/{$city}', [App\Http\Controllers\Api\Frontend\Mobile\ProductController::class, 'ProductDetailPageMobileRegionalNew'])->name('api.ProductDetailPageMobileRegionalNew');
    
    Route::get('home-page-mob', [App\Http\Controllers\Api\Frontend\Mobile\HomePageApiController::class, 'HomePageFrontend'])->name('api.MobHomePageFrontend');
    Route::get('home-page-mob-regional', [App\Http\Controllers\Api\Frontend\Mobile\HomePageApiController::class, 'HomePageFrontendRegional'])->name('api.HomePageFrontendRegional');
    
    Route::get('home-page-mob-regional-new/{$city}', [App\Http\Controllers\Api\Frontend\Mobile\HomePageApiController::class, 'HomePageFrontendRegionalNew'])->name('api.HomePageFrontendRegionalNew');
    
    Route::get('mob-cat-listing', [App\Http\Controllers\Api\Frontend\Mobile\CategoryController::class, 'MobCategoryListing'])->name('api.MobCategoryListing');
    Route::get('mobile-setting-data', [App\Http\Controllers\Api\Frontend\Mobile\MobileSettingApiController::class, 'SettingData'])->name('api.SettingData');
    Route::get('/get-mob-regions/{lang?}', [App\Http\Controllers\Api\Frontend\Mobile\AddressApiController::class, 'getMobRegData'])->name('api.getMobRegData');
    Route::get('/get-shipping-data/{id}', [App\Http\Controllers\Api\Frontend\Mobile\AddressApiController::class, 'getShippingData'])->name('api.getShippingData');
    Route::get('/get-city-list', [App\Http\Controllers\Api\Frontend\Mobile\AddressApiController::class, 'getCityList'])->name('api.getCityList');
    Route::get('/get-city-list-lang/{lang}', [App\Http\Controllers\Api\Frontend\Mobile\AddressApiController::class, 'getCityListLang'])->name('api.getCityListLang');
    Route::get('/get-address-data/{id}/{lang?}', [App\Http\Controllers\Api\Frontend\Mobile\AddressApiController::class, 'getMobAddressData'])->name('api.getMobAddressData');
    Route::get('/hyperpaymentnew-mobile/{orderid}/{lang}', [App\Http\Controllers\Api\Frontend\OrderController::class, 'hyperpaynewMobile'])->name('api.hyperpaymentnewMobile');
    Route::post('/mob-new-projectsale', [App\Http\Controllers\Api\Frontend\Mobile\ProjectSalesApiController::class, 'AddProjectSale'])->name('api.AddProjectSale');
    Route::post('/mob-new-contactus', [App\Http\Controllers\Api\Frontend\Mobile\ProjectSalesApiController::class, 'AddContactUs'])->name('api.AddContactUs');
    Route::get('/mob-stores/{lang?}', [App\Http\Controllers\Api\Frontend\Mobile\StoreLocatorController::class, 'getMobStoreLocators'])->name('api.getMobStoreLocators');
    Route::get('/mob-user-data/{id}', [App\Http\Controllers\Api\Frontend\Mobile\MaintainanceController::class, 'getUserDataMob'])->name('api.getUserDataMob');
    Route::post('/getproductsdetailmaint', [App\Http\Controllers\Api\Frontend\Mobile\MaintainanceController::class, 'getProductDataByid'])->name('api.getProductDataByidMain');
    Route::post('/mob-add-maintainance', [App\Http\Controllers\Api\Frontend\Mobile\MaintainanceController::class, 'StoreMaintainance'])->name('api.StoreMaintainance');
    Route::get('/project-sale-data', [App\Http\Controllers\Api\Frontend\Mobile\ProjectSalesApiController::class, 'ProjectSaleData'])->name('api.ProjectSaleData');
    
    
    Route::get('/get-city-dropdown', [App\Http\Controllers\Api\Frontend\Mobile\AddressApiController::class, 'getCityDropdown'])->name('api.getCityDropdown');
    
    
    Route::get('/getdiscounttype', [App\Http\Controllers\Api\Frontend\ProductController::class, 'ProductDiscountType'])->name('api.ProductDiscountType');
    Route::get('/notifications', [App\Http\Controllers\Api\NotificationApiController::class, 'FrontendNotificationsList'])->name('api.FrontendNotificationsList');
    Route::get('/latestnotification', [App\Http\Controllers\Api\NotificationApiController::class, 'FrontendLatestNotification'])->name('api.FrontendLatestNotification');
    
    
    
    
    Route::post('/recheckdata', [App\Http\Controllers\Api\Frontend\CheckoutController::class, 'recheckdata'])->name('api.recheckdata');
    Route::post('/recheckdata-regional', [App\Http\Controllers\Api\Frontend\CheckoutController::class, 'recheckdataRegional'])->name('api.recheckdataRegional');
    
    Route::post('/recheckdata-regional-new', [App\Http\Controllers\Api\Frontend\CheckoutController::class, 'recheckdataRegionalNew'])->name('api.recheckdataRegionalNew');
    Route::post('/recheckdata-regional-new-duplicate', [App\Http\Controllers\Api\Frontend\CheckoutController::class, 'recheckdataRegionalNewDuplicate'])->name('api.recheckdataRegionalNewDuplicate');
    
    Route::get('/hyperresponse/{orderid}/{id}', [App\Http\Controllers\Api\Frontend\OrderController::class, 'hyperpaypaymentresponse'])->name('api.hyperpaypaymentresponse');
    Route::get('/mispayresponse/{orderid}/{id}', [App\Http\Controllers\Api\Frontend\OrderController::class, 'mispaypaymentresponse'])->name('api.mispaypaymentresponse');
    
    // home page sections api
    Route::get('/homepage-sec-one', [App\Http\Controllers\Api\Frontend\HomePageController::class, 'SecOne'])->name('api.SecOne');
    Route::get('/homepage-sec-two', [App\Http\Controllers\Api\Frontend\HomePageController::class, 'SecTwo'])->name('api.SecTwo');
    Route::get('/homepage-sec-three', [App\Http\Controllers\Api\Frontend\HomePageController::class, 'SecThree'])->name('api.SecThree');
    Route::get('/homepage-sec-four', [App\Http\Controllers\Api\Frontend\HomePageController::class, 'SecFour'])->name('api.SecFour');
    Route::get('/homepage-sec-five', [App\Http\Controllers\Api\Frontend\HomePageController::class, 'SecFive'])->name('api.SecFive');
    Route::get('/homepage-sec-six', [App\Http\Controllers\Api\Frontend\HomePageController::class, 'SecSix'])->name('api.SecSix');
    Route::get('/homepage-sec-seven', [App\Http\Controllers\Api\Frontend\HomePageController::class, 'SecSeven'])->name('api.SecSeven');
    Route::get('/homepage-sec-eight', [App\Http\Controllers\Api\Frontend\HomePageController::class, 'SecEight'])->name('api.SecEight');
    Route::get('/homepage-sec-nine', [App\Http\Controllers\Api\Frontend\HomePageController::class, 'SecNine'])->name('api.SecNine');
    Route::get('/homepage-sec-ten', [App\Http\Controllers\Api\Frontend\HomePageController::class, 'SecTen'])->name('api.SecTen');
    Route::get('/homepage-sec-eleven', [App\Http\Controllers\Api\Frontend\HomePageController::class, 'SecEleven'])->name('api.SecEleven');
    Route::get('/homepage-sec-twelve', [App\Http\Controllers\Api\Frontend\HomePageController::class, 'SecTwelve'])->name('api.SecTwelve');
    Route::get('/homepage-sec-thirteen', [App\Http\Controllers\Api\Frontend\HomePageController::class, 'SecThirteen'])->name('api.SecThirteen');
   
    Route::get('/testingnew-noti', [App\Http\Controllers\Api\Frontend\GlobalController::class, 'testingNoti'])->name('frontend.testingNoti');
    
    Route::get('/getERP_orders', [App\Http\Controllers\Api\Frontend\GlobalController::class, 'getERPOrders'])->name('frontend.getERPOrders');
    Route::post('/submit-newslatter', [App\Http\Controllers\Api\Frontend\NewsLatterController::class, 'submitNewslatter'])->name('api.submitNewslatter');
    Route::get('/update-newslatter/{id}', [App\Http\Controllers\Api\Frontend\NewsLatterController::class, 'updateNewslatter'])->name('api.updateNewslatter');
    Route::post('/userDelete', [App\Http\Controllers\Api\Frontend\UserController::class, 'userDelete'])->name('api.userDelete');
    
    
    Route::post('/notificationsCounts', [App\Http\Controllers\Api\Frontend\GlobalController::class, 'NotificationCounts'])->name('api.NotificationCounts');
    Route::get('/POEmailSend', [App\Http\Controllers\Api\Frontend\GlobalController::class, 'POEmailSend'])->name('api.POEmailSend');
    Route::get('/storecacheflush/{type}', [App\Http\Controllers\Api\Frontend\GlobalController::class, 'storeCacheFlush'])->name('api.storeCacheFlush');
    Route::get('/deletecachecycle', [App\Http\Controllers\Api\Frontend\GlobalController::class, 'deleteCacheCycle'])->name('api.deleteCacheCycle');
    Route::get('/cacheflushbykey/{key}', [App\Http\Controllers\Api\Frontend\GlobalController::class, 'cacheFlushByKey'])->name('api.cacheFlushByKey');
    
    
    
    Route::get('/homepagepartone', [App\Http\Controllers\Api\Frontend\HomePageController::class, 'HomePagePartOne'])->name('api.HomePagePartOne');
    Route::get('/homepageparttwo', [App\Http\Controllers\Api\Frontend\HomePageController::class, 'HomePagePartTwo'])->name('api.HomePagePartTwo');
    Route::get('/homepagepartthree', [App\Http\Controllers\Api\Frontend\HomePageController::class, 'HomePagePartThree'])->name('api.HomePagePartThree');
    
    Route::get('/getERP_livestock', [App\Http\Controllers\Api\Frontend\GlobalController::class, 'getERPLiveStock'])->name('api.getERPLiveStock');
    Route::get('/getProduct_livestock', [App\Http\Controllers\Api\Frontend\GlobalController::class, 'getProductlivestock'])->name('api.getProductlivestock');

    Route::get('/homepagetwo-secone', [App\Http\Controllers\Api\Frontend\HomePageTwoController::class, 'homePageTwoSecOne'])->name('api.HomePageTwoSecOne');
    Route::get('/homepagetwo-sectwo', [App\Http\Controllers\Api\Frontend\HomePageTwoController::class, 'homePageTwoSecTwo'])->name('api.HomePageTwoSecTwo');
    Route::get('/homepagetwo-secthree', [App\Http\Controllers\Api\Frontend\HomePageTwoController::class, 'homePageTwoSecThree'])->name('api.HomePageTwoSecThree');

    Route::get('/pickup-from-store/{prod_id}/{city}/{type}', [App\Http\Controllers\Api\Frontend\PickupFromStoreController::class, 'index'])->name('api.pickupFromStore');
    Route::get('/pickup-store-status-update/{order_id}/{status}', [App\Http\Controllers\Api\Frontend\PickupFromStoreController::class, 'pickupStoreStatusUpdate'])->name('api.PickupFromStoreController');
    Route::get('/get-warehouse', [App\Http\Controllers\Api\Frontend\PickupFromStoreController::class, 'getWarehouse'])->name('api.getWarehouse');
    Route::post('/get-warehouseCart', [App\Http\Controllers\Api\Frontend\PickupFromStoreController::class, 'getwarehouseCart'])->name('api.getwarehouseCart');
    Route::get('/get-selected-warehouse/{id}', [App\Http\Controllers\Api\Frontend\PickupFromStoreController::class, 'getSelectedWarehouse'])->name('api.getSelectedWarehouse');
    Route::get('/get-user-loyalty-data/{id}', [App\Http\Controllers\Api\Frontend\LoyaltyPointsApiController::class, 'getUserLoyaltyData'])->name('api.getUserLoyaltyData');
    Route::get('/get-user-loyalty-data-history/{id}', [App\Http\Controllers\Api\Frontend\LoyaltyPointsApiController::class, 'getERPUserLoyaltyDataHisoty'])->name('api.getERPUserLoyaltyDataHisoty');
    Route::post('/get-available-date-delivery', [App\Http\Controllers\Api\Frontend\SlotManagementController::class, 'checkAvailableDateForDelivery'])->name('api.checkAvailableDateForDelivery');
    Route::post('/get-shipment-available-date-delivery', [App\Http\Controllers\Api\Frontend\SlotManagementController::class, 'checkShipmentAvailableDateForDelivery'])->name('api.checkShipmentAvailableDateForDelivery');
    
    
});

Route::post('/livestockdata/{sku?}', [App\Http\Controllers\Api\Frontend\LiveStockDataController::class, 'LiveStockDataFunction'])->name('api.LiveStockDataFunction');
Route::post('/livestockupdatedata', [App\Http\Controllers\Api\Frontend\LiveStockDataController::class, 'LiveStockDataFunctionUpdate'])->name('api.LiveStockDataFunctionUpdate');
Route::post('/updatelivestockdata', [App\Http\Controllers\Api\Frontend\LiveStockDataController::class, 'updateLivesSockData'])->name('api.updateLivesSockData');
Route::get('/generatesitemap', [App\Http\Controllers\Api\SitemapController::class, 'generatesitemap'])->name('api.generatesitemap');
Route::get('/productdataxml/marketplace', [App\Http\Controllers\Api\ExtraApisController::class, 'productDataXml'])->name('api.productDataXml');
Route::get('/apitesting', [App\Http\Controllers\Api\ExtraApisController::class, 'apiTesting'])->name('api.apiTesting');
Route::get('/RTB', [App\Http\Controllers\Api\ExtraApisController::class, 'getProductsData']);
Route::get('/webengage/{lang}', [App\Http\Controllers\Api\Frontend\GlobalController::class, 'webengageProductFeedData'])->name('api.webengageProductFeedData');
Route::get('/update-amazon-stock', [App\Http\Controllers\Api\UserApiController::class, 'updateAmazonStock'])->name('api.updateAmazonStock');