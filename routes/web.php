<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SourceController;
use App\Http\Controllers\QaController;
use App\Http\Controllers\GraphOpsController;


Route::get('/graphops/admin', [GraphOpsController::class, 'index'])->name('graphops.admin');
Route::get('/graphops/status', [GraphOpsController::class, 'status'])->name('graphops.status');
Route::post('/graphops/rebuild', [GraphOpsController::class, 'rebuild'])->name('graphops.rebuild');
Route::post('/graphops/cancel', [GraphOpsController::class, 'cancel'])->name('graphops.cancel');

Route::get('/', [DashboardController::class,'index'])->name('dashboard');
Route::post('/sources', [SourceController::class,'store'])->name('sources.store');
Route::post('/qa', [QaController::class,'ask'])->name('qa.ask');
