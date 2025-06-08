import { Component, OnInit } from "@angular/core";
import { Router } from "@angular/router";
import { CommonModule } from "@angular/common";
import { AuthService } from "../../services/auth.service";
import { TrainerService, Usuario } from "../../services/trainer.service";
import { RutinaService, Rutina } from "../../services/rutina.service";
import { forkJoin, max } from "rxjs";

interface StatCard {
    title: string;
    value: number;
    subtitle: string;
    icon: string;
    color: string;
}

interface ChartData {
    label: string;
    value: number;
    color: string;
    percentage: number;
}

@Component ({
    selector: 'app-trainer-stats',
    standalone: true,
    imports: [CommonModule],
    templateUrl: './trainer-stats.component.html'
})
export class TrainerStatsComponent implements OnInit {
    currentTrainer: any;
    allRoutines: Rutina[] = [];
    myClients: Usuario[] = [];

    loading = true;
    error: string | null = null;

    // Datos para las tarjetas de estadísticas
    statCards: StatCard[] = [];

    // Datos para los gráficos
    rutinasByStatus: ChartData[] = [];
    rutinasByType: ChartData[] = [];
    rutinasByCategory: ChartData[] = [];
    clientsActivity: ChartData[] = [];

    constructor (
        private authService: AuthService,
        private trainerService: TrainerService,
        private rutinaService: RutinaService,
        private router: Router
    ) {}

    ngOnInit(): void {
        this.currentTrainer = this.authService.getCurrentUser();
        this.loadData();
    }

    loadData(): void {
        this.loading = true;
        this.error = null;

        forkJoin({
            trainer: this.trainerService.getTrainerById(this.currentTrainer.id)
        }).subscribe({
            next: (data: any) => {
                const trainer = Array.isArray(data.trainer) ? data.trainer[0] : data.trainer;
                this.myClients = trainer?.usuarios || [];
                this.allRoutines = trainer?.rutinasCreadas || [];

                this.calculateStadistics();
                this.loading = false;
            }, 
            error: (error) => {
                console.error('Error loading data:', error);
                this.error = 'Error al cargar las estadísticas';
                this.loading = false;
            }
        });
    }

    calculateStadistics(): void {
        this.calculateStatCards();
        this.calculateRutinasByStatus();
        this.calculateRutinasByType();
        this.calculateRutinasByCategory();
        this.calculateClientsActivity();
    }

    calculateStatCards(): void {
        const totalRoutines = this.allRoutines.length;
        const completedRoutines = this.allRoutines.filter(r => this.getRoutineStatus(r) === 'completed').length;
        const activeClients = this.myClients.length;
        const totalExercises = this.allRoutines.reduce((sum, routine) => 
        sum + (routine.rutinaEjercicios?.length || 0), 0);

        this.statCards = [
            {
              title: 'Total Rutinas',
              value: totalRoutines,
              subtitle: 'Rutinas creadas',
              icon: '📋',
              color: 'blue'
            },
            {
              title: 'Rutinas Completadas',
              value: completedRoutines,
              subtitle: `${totalRoutines > 0 ? Math.round((completedRoutines / totalRoutines) * 100) : 0}% del total`,
              icon: '✅',
              color: 'green'
            },
            {
              title: 'Clientes Activos',
              value: activeClients,
              subtitle: 'Usuarios asignados',
              icon: '👥',
              color: 'purple'
            },
            {
              title: 'Total Ejercicios',
              value: totalExercises,
              subtitle: 'En todas las rutinas',
              icon: '💪',
              color: 'orange'
            }
          ];
        }

    calculateRutinasByStatus(): void {
        const statusCount = {
            pending: 0,
            completed: 0,
            active: 0
        };
        
        this.allRoutines.forEach(routine => {
            const status = this.getRoutineStatus(routine);
            statusCount[status as keyof typeof statusCount]++;
        });
        
        const total = this.allRoutines.length;
        const colors = ['#FCD34D', '#10B981', '#3B82F6']; // yellow, green, blue
        
            this.rutinasByStatus = [
            {
                label: 'Pendientes',
                value: statusCount.pending,
                color: colors[0],
                percentage: total > 0 ? Math.round((statusCount.pending / total) * 100) : 0
            },
            {
                label: 'Completadas',
                value: statusCount.completed,
                color: colors[1],
                percentage: total > 0 ? Math.round((statusCount.completed / total) * 100) : 0
            },
            {
                label: 'En Progreso',
                value: statusCount.active,
                color: colors[2],
                percentage: total > 0 ? Math.round((statusCount.active / total) * 100) : 0
            }
            ];
        }

    calculateRutinasByType(): void {
        const typeCount: { [key: string]: number } = {};
            
        this.allRoutines.forEach(routine => {
        const type = routine.tipo_rutina || 'Sin tipo';
        typeCount[type] = (typeCount[type] || 0) + 1;
        });
        
        const colors = ['#EF4444', '#F59E0B', '#10B981', '#3B82F6', '#8B5CF6', '#EC4899'];
        const total = this.allRoutines.length;
        
        this.rutinasByType = Object.entries(typeCount).map(([type, count], index) => ({
            label: type,
            value: count,
            color: colors[index % colors.length],
            percentage: total > 0 ? Math.round((count / total) * 100) : 0
        }));
    }

    calculateRutinasByCategory(): void {
        const categoryCount: { [key: string]: number } = {};
            
        this.allRoutines.forEach(routine => {
            const category = routine.categoria || 'Sin categoría';
            categoryCount[category] = (categoryCount[category] || 0) + 1;
        });
        
        const colors = ['#22C55E', '#F59E0B', '#EF4444', '#8B5CF6'];
        const total = this.allRoutines.length;
        
        this.rutinasByCategory = Object.entries(categoryCount).map(([category, count], index) => ({
            label: category,
            value: count,
            color: colors[index % colors.length],
              percentage: total > 0 ? Math.round((count / total) * 100) : 0
            }));
        }

    calculateClientsActivity(): void {
        const clientRoutineCount: { [key: string]: number } = {};
            
        this.allRoutines.forEach(routine => {
            if (routine.usuarioId) {
                const client = this.myClients.find(c => c.id === routine.usuarioId);
                if (client) {
        const clientName = `${client.nombre} ${client.apellidos}`;
            clientRoutineCount[clientName] = (clientRoutineCount[clientName] || 0) + 1;
            }
        }
    });

    const colors = ['#3B82F6', '#10B981', '#F59E0B', '#EF4444', '#8B5CF6'];
    const total = Object.values(clientRoutineCount).reduce((sum, count) => sum + count, 0);

        this.clientsActivity = Object.entries(clientRoutineCount)
            .sort(([,a], [,b]) => b - a) // Ordenar por cantidad descendente
            .map(([client, count], index) => ({
        label: client,
        value: count,
        color: colors[index % colors.length],
        percentage: total > 0 ? Math.round((count / total) * 100) : 0
        }));
    }

    getRoutineStatus(routine: Rutina): string {
        if (routine.resultado_entreno) {
            return routine.resultado_entreno.completado ? 'completed' : 'active';
        }
        return 'pending';
    }

    goBackToDashboard(): void {
        this.router.navigate(['/trainer-dashboard']);
    }

    getMaxValue(data: ChartData[]): number {
        return Math.max(...data.map(item => item.value), 1);
    }

    getBarWitdh(value: number, maxValue: number): number {
        return (value / maxValue) * 100;
    }
}