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
        loadComponent: () => import('./components/rutina-detalle/rutina-detalle.component').then(m => m.RutinaDetalleComponent),
        canActivate: [ClientGuard]
    },
    {
        path: '', 
        redirectTo: '/login',
        pathMatch: 'full'
    }
];
