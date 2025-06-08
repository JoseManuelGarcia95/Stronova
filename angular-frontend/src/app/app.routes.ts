import { Routes } from '@angular/router';
import { TrainerGuard } from './guards/trainer.guards';
import { ClientGuard } from './guards/client.guards';

export const routes: Routes = [
    {
        path: 'login', 
        loadComponent: () => import('./login/login.component').then(m => m.LoginComponent)
    },
    {
        path: 'register',
        loadComponent: () => import('./register/register.component').then(m => m.RegisterComponent)
    },
    {
        path: 'trainer-dashboard',
        loadComponent: () => import('./trainer-dashboard/trainer-dashboard.component').then(m => m.TrainerDashboardComponent),
        canActivate: [TrainerGuard]
    }, 
    {
        path: 'client-dashboard',
        loadComponent: () => import('./client-dashboard/client-dashboard.component').then(m => m.ClientDashboardComponent),
        canActivate: [ClientGuard]
    }, 
    {
        path: 'rutina-detalle/:id',
        loadComponent: () => import('./components/rutina-detalle/rutina-detalle.component').then(m => m.RutinaDetalleComponent)
    },
    {
        path: 'trainer/clients',
        loadComponent: () => import('./trainer/trainer-clients/trainer-clients.component').then(m => m.TrainerClientsComponent),
        canActivate: [TrainerGuard]
    }, 
    {
        path: 'trainer/create-routine',
        loadComponent: () => import('./trainer/create-routine/create-routine.component').then(m => m.CreateRoutineComponent),
        canActivate: [TrainerGuard]
    },
    {
        path: 'trainer/routines',
        loadComponent: () => import('./trainer/trainer-routines/trainer-routines.component').then(m => m.TrainerRoutinesComponent),
        canActivate: [TrainerGuard]
    },
    {
        path: 'trainer/stats',
        loadComponent: () => import('./trainer/trainer-stats/trainer-stats.component').then(m => m.TrainerStatsComponent),
        canActivate: [TrainerGuard]
    }, 
    {
        path: 'trainer/edit-routine/:id',
        loadComponent: () => import('./trainer/create-routine/create-routine.component').then(m => m.CreateRoutineComponent),
        canActivate: [TrainerGuard]
    },
    {
        path: '', 
        redirectTo: '/login',
        pathMatch: 'full'
    }
];
