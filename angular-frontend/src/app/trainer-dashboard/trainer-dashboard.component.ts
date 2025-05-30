import { Component } from '@angular/core';
import { AuthService } from '../services/auth.service';
import { CommonModule } from '@angular/common';
import { Router } from '@angular/router';

@Component({
  selector: 'app-trainer-dashboard',
  templateUrl: './trainer-dashboard.component.html',
  standalone: true,
  imports: [ CommonModule]
})
export class TrainerDashboardComponent {
  currentUser: any;

  // Menú navegación principal
  navigationCards = [
    {
      title: 'Gestionar Clientes',
      description: 'Ver, asignar y gestionar mis clientes',
      icon: 'users',
      route: '/trainer/clients',
      color: 'bg-blue-500',
      hoverColor: 'hover:bg-blue-600'
    }, 
    {
      title: 'Crear Rutinas',
      description: 'Diseñar nuevas rutinas de entrenamiento',
      icon: 'plus-circle',
      route: '/trainer/create-routine',
      color: 'bg-green-500',
      hoverColor: 'hover:bg-green-600'
    },
    {
      title: 'Mis Rutinas',
      description: 'Ver y gestionar rutinas creadas',
      icon: 'clipboard-list',
      route: '/trainer/routines',
      color: 'bg-purple-500',
      hoverColor: 'hover:bg-purple-600'
    },
    {
      title: 'Estadísticas',
      description: 'Progreso y análisis de entrenamientos',
      icon: 'chart-bar',
      route: '/trainer/stats',
      color: 'bg-orange-500',
      hoverColor: 'hover:bg-orange-600'
    }
  ];

  constructor(
    private authService: AuthService,
    private router: Router
  ) {
    this.currentUser = this.authService.getCurrentUser();
  }

  navigateTo(route: string): void {
    this.router.navigate([route]);
  }

  // Obtención de los SVG
  getIconSvg(iconName: string): string {
    const icons: { [key: string]: string } = {
      'users': `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM9 9a2 2 0 11-4 0 2 2 0 014 0z" />`,
      'plus-circle': `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />`,
      'clipboard-list': `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />`,
      'chart-bar': `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />`
    };
    return icons[iconName] || '';
  }

  logout(): void {
    this.authService.logout();
  }
}