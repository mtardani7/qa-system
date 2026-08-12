<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Plant;
use App\Models\DailyReport;
use App\Models\Line;
use App\Models\Machine;
use App\Models\Shift;
use App\Models\Product;
use App\Models\Defect;
use App\Models\QaChecker;
use App\Models\DailyReportExport;
use App\Policies\DailyReportPolicy;
use App\Policies\LinePolicy;
use App\Policies\MachinePolicy;
use App\Policies\ShiftPolicy;
use App\Policies\ProductPolicy;
use App\Policies\DefectPolicy;
use App\Policies\QaCheckerPolicy;
use App\Policies\DailyReportExportPolicy;
use App\Repositories\Contracts\DailyReportRepositoryInterface;
use App\Repositories\Contracts\DailyReportDefectRepositoryInterface;
use App\Repositories\DailyReportRepository;
use App\Repositories\DailyReportDefectRepository;
use App\Repositories\LineRepository;
use App\Repositories\MachineRepository;
use App\Repositories\ShiftRepository;
use App\Repositories\ProductRepository;
use App\Repositories\DefectRepository;
use App\Repositories\QaCheckerRepository;
use App\Policies\PlantPolicy;
use App\Repositories\Contracts\PlantRepositoryInterface;
use App\Repositories\Contracts\LineRepositoryInterface;
use App\Repositories\Contracts\MachineRepositoryInterface;
use App\Repositories\Contracts\ShiftRepositoryInterface;
use App\Repositories\Contracts\ProductRepositoryInterface;
use App\Repositories\Contracts\DefectRepositoryInterface;
use App\Repositories\Contracts\QaCheckerRepositoryInterface;
use App\Repositories\Contracts\DashboardRepositoryInterface;
use App\Repositories\PlantRepository;
use App\Repositories\DashboardRepository;
use Illuminate\Support\Facades\Gate;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use App\Models\Company;
use App\Models\Department;
use App\Models\Holiday;
use App\Models\SystemSetting;
use App\Policies\EnterprisePolicy;
use App\Policies\UserPolicy;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(PlantRepositoryInterface::class, PlantRepository::class);
        $this->app->bind(DailyReportRepositoryInterface::class, DailyReportRepository::class);
        $this->app->bind(DailyReportDefectRepositoryInterface::class, DailyReportDefectRepository::class);
        $this->app->bind(LineRepositoryInterface::class, LineRepository::class);
        $this->app->bind(MachineRepositoryInterface::class, MachineRepository::class);
        $this->app->bind(ShiftRepositoryInterface::class, ShiftRepository::class);
        $this->app->bind(ProductRepositoryInterface::class, ProductRepository::class);
        $this->app->bind(DefectRepositoryInterface::class, DefectRepository::class);
        $this->app->bind(QaCheckerRepositoryInterface::class, QaCheckerRepository::class);
        $this->app->bind(DashboardRepositoryInterface::class, DashboardRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RateLimiter::for('login', function (Request $request): Limit {
            return Limit::perMinute(5)->by(strtolower((string) $request->input('email')).'|'.$request->ip());
        });
        RateLimiter::for('api', function (Request $request): Limit {
            return Limit::perMinute(120)->by((string) ($request->user()?->getAuthIdentifier() ?? $request->ip()));
        });
        Gate::policy(Plant::class, PlantPolicy::class);
        Gate::policy(DailyReport::class, DailyReportPolicy::class);
        Gate::policy(Line::class, LinePolicy::class);
        Gate::policy(Machine::class, MachinePolicy::class);
        Gate::policy(Shift::class, ShiftPolicy::class);
        Gate::policy(Product::class, ProductPolicy::class);
        Gate::policy(Defect::class, DefectPolicy::class);
        Gate::policy(QaChecker::class, QaCheckerPolicy::class);
        Gate::policy(DailyReportExport::class, DailyReportExportPolicy::class);
        Gate::policy(\App\Models\User::class, UserPolicy::class);
        Gate::policy(Company::class, EnterprisePolicy::class);
        Gate::policy(Department::class, EnterprisePolicy::class);
        Gate::policy(Holiday::class, EnterprisePolicy::class);
        Gate::policy(SystemSetting::class, EnterprisePolicy::class);
    }
}
