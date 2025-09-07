<?php

use App\Http\Controllers\HomeController;
use App\Http\Middleware\AdminAuth;
use App\Livewire\Admin\Access\PermissionManager;
use App\Livewire\Admin\Access\RoleManager;
use App\Livewire\Admin\Analytics\AnalyticsIndex;
use App\Livewire\Admin\Author\AuthorManager;
use App\Livewire\Admin\Book\BookForm;
use App\Livewire\Admin\Book\BookList;
use App\Livewire\Admin\Category\CategoryManager;
use App\Livewire\Admin\Composer\ComposerManager;
use App\Livewire\Admin\Dashboard\DashboardIndex;
use App\Livewire\Admin\Editor\EditorManager;
use App\Livewire\Admin\Narrator\NarratorManager;
use App\Livewire\Admin\Payment\PaymentImporter;
use App\Livewire\Admin\Payment\PaymentManager;
use App\Livewire\Admin\Publisher\PublisherManager;
use App\Livewire\Admin\Translator\TranslatorManager;
use App\Livewire\Admin\User\SessionManager;
use App\Livewire\Admin\User\UserManager;
use App\Livewire\Auth\PasswordLogin;
use Illuminate\Support\Facades\Route;

Route::get('login', PasswordLogin::class)->name('login');

Route::get('logout', [HomeController::class, 'logout'])->name('logout');


Route::get('/', [HomeController::class, 'index'])->name('home.inedx');
Route::get('logout', [HomeController::class, 'logout'])->name('logout');
Route::get('test', [HomeController::class, 'test'])->name('test');


Route::prefix('admin')->middleware([AdminAuth::class])->group(function () {


    Route::get('dashboard', DashboardIndex::class)->name('dashboard.index');

    Route::middleware('permission:مدیریت کاربران')->group(function () {
        Route::get('user', UserManager::class)->name('user.index');
        Route::get('session', SessionManager::class)->name('session.index');
    });

    Route::middleware('role:ادمین اصلی')->group(function () {
        Route::get('role', RoleManager::class)->name('role.index');
        Route::get('permission', PermissionManager::class)->name('permission.index');

    });

    Route::middleware('permission:مدیریت دسته بندی ها')->group(function () {
        Route::get('category', CategoryManager::class)->name('category.index');
    });
    Route::middleware('permission:مدیریت نویسندگان')->group(function () {
        Route::get('author', AuthorManager::class)->name('author.index');
    });
    Route::middleware('permission:مدیریت مترجمان')->group(function () {
        Route::get('translator', TranslatorManager::class)->name('translator.index');
    });

    Route::middleware('permission:مدیریت گویندگان')->group(function () {
        Route::get('narrator', NarratorManager::class)->name('narrator.index');
    });
    Route::middleware('permission:مدیریت آهنگسازان')->group(function () {
        Route::get('composer', ComposerManager::class)->name('composer.index');
    });
    Route::middleware('permission:مدیریت تدوینگران')->group(function () {
        Route::get('editor', EditorManager::class)->name('editor.index');
    });
    Route::middleware('permission:مدیریت ناشران')->group(function () {
        Route::get('publisher', PublisherManager::class)->name('publisher.index');
    });

    Route::middleware('permission:مدیریت کتاب‌ها')->group(function () {
        Route::get('book', BookList::class)->name('book.index');
        Route::get('book-create', BookForm::class)->name('book.create');
        Route::get('book-edit/{book}', BookForm::class)->name('book.edit');
    });

    Route::middleware('permission:مدیریت پرداخت ها')->group(function () {
        Route::get('payment', PaymentManager::class)->name('payment.index');
    });

    Route::middleware('permission:مدیریت ایمپورت از پلتفرم ها')->group(function () {
        Route::get('payment/import', PaymentImporter::class)->name('payment.import');
    });

    Route::middleware('permission:مدیریت بخش آنالیز')->group(function () {
        Route::get('analytics', AnalyTicsIndex::class)->name('analytics.index');
    });


});
