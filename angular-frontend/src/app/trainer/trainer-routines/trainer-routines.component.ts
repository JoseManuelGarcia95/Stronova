import { Component, OnInit } from "@angular/core";
import { Router, ActivatedRoute } from "@angular/router";
import { CommonModule } from "@angular/common";
import { FormsModule } from "@angular/forms";
import { AuthService } from "../../services/auth.service";
import { TrainerService, Usuario } from "../../services/trainer.service";
import { RutinaService, Rutina } from "../../services/rutina.service";
import { forkJoin } from "rxjs";

@Component ({
    selector: 'app-trainer-routines',
    standalone: true,
    imports: [CommonModule, FormsModule],
    templateUrl: './trainer-routines.component.html'
})
export class TrainerRoutinesComponent implements OnInit {
    currentTrainer: any;
    allRoutines: Rutina[] = [];
    filteredRoutines: Rutina[] = [];
    myClients: Usuario[] = [];

    loading = true;
    error : string | null = null;

    // Filtros
    searchTerm = '';
    filterByClient = '';
    filterByType = '';
    filterByCategory = '';
    filterByStatus = '';

    // Cliente específico
    specificClientId: number | null = null;
    specificClient: Usuario | null = null;

    // Modales
    showDeleteModal = false;
    selectedRoutine: Rutina | null = null;

    // Tipos disponibles para filtros
    availableTypes = ['Fuerza', 'Resistencia', 'Funcional', 'Rehabilitación', 'Cardio', 'Flexibilidad'];
    availableCategories = ['Principiante', 'Intermedio', 'Avanzado', 'Terapéutica'];
    statusOptions = [
        { value: '', label: 'Todos'},
        { value: 'pending', label: 'Pendientes'},
        { value: 'completed', label: 'Completado'},
        { value: 'active', label: 'Activas'}
    ];

    constructor(
        private authService: AuthService,
        private trainerService: TrainerService,
        private rutinaService: RutinaService,
        private router: Router,
        private route: ActivatedRoute
    ) {}

    ngOnInit(): void {
        this.currentTrainer = this.authService.getCurrentUser();

        this.route.queryParams.subscribe(params => {
            if (params['clientId']) {
                this.specificClientId = parseInt(params['clientId']);
                this.filterByClient = this.specificClientId.toString();
            }
        });

        this.loadData();
    }

    loadData(): void {
        this.loading = true;
        this.error = null;

        forkJoin({
            trainer: this.trainerService.getTrainerById(this.currentTrainer.id)
        }).subscribe({
            next: (data: any) => {
                // Extraer cliente
                const trainer = Array.isArray(data.trainer) ? data.trainer[0] : data.trainer;
                this.myClients = trainer?.usuarios || [];

                // Extraer Rutinas
                this.allRoutines = trainer?.rutinasCreadas || [];

                // ✅ Debug para ver qué datos llegan
            console.log('Rutinas cargadas:', this.allRoutines);
            this.allRoutines.forEach((routine, index) => {
                console.log(`Rutina ${index}:`, routine);
                if (!routine.usuarioId) {
                    console.warn(`Rutina ${routine.nombre} no tiene usuario_id`);
                }
            });

                // Si hay un clientes específico, encontrarlo
                if (this.specificClientId) {
                    this.specificClient = this.myClients.find(c => c.id === this.specificClientId) || null;
                }

                this.applyFilters();
                this.loading = false;
            },
            error: (error) => {
                console.error('Error loading data:', error);
                this.error = 'Error al cargar las rutinas';
                this.loading = false;
            }
        });
    }

    // Aplicar filtros
    applyFilters(): void {
        this.filteredRoutines = this.allRoutines.filter(routine => {
            const matchesSearch = !this.searchTerm ||
                routine.nombre.toLowerCase().includes(this.searchTerm.toLowerCase()) ||
                routine.descripcion?.toLowerCase().includes(this.searchTerm.toLowerCase());

            const matchesClient = !this.filterByClient ||
                routine.usuarioId?.toString() === this.filterByClient;
            
            const matchesType = !this.filterByType ||
                routine.tipo_rutina === this.filterByType;

            const matchesCategory = !this.filterByCategory ||
                routine.categoria === this.filterByCategory;

            const matchesStatus = !this.filterByStatus ||
                this.getRoutineStatus(routine) === this.filterByStatus;

            return matchesSearch && matchesClient && matchesType && matchesCategory && matchesStatus;
        });
    }

    // Determinar el estado de una rutina
    getRoutineStatus(routine: Rutina): string {
        if (routine.resultado_entreno) {
            return routine.resultado_entreno.completado ? 'completed': 'active';
        }
        return 'pending';
    }

    getRoutineStatusText(routine: Rutina): string {
        const status = this.getRoutineStatus(routine);
        switch (status) {
            case 'completed': return 'Completada';
            case 'active': return 'En progreso';
            case 'pending': return 'Pendiente';
            default: return 'Desconocido';
        }
    }

    getRoutineStatusClass(routine: Rutina): string {
        const status = this.getRoutineStatus(routine);
        switch (status) {
            case 'completed': return 'bg-green-100 text-green-800';
            case 'active': return 'bg-blue-100 text-blue-800';
            case 'pending': return 'bg-yellow-100 text-yellow-800';
            default: return 'bg-gray-100 text-gray-800'; 
        }
    }

    // Obtener nombre del cliente
    getClientName(userId: number): string {
        if (!userId) return 'Sin cliente asignado';
        const client = this.myClients.find(c => c.id === userId);
        return client ? `${client.nombre} ${client.apellidos}` : 'Cliente no encontrado';
    }

    // Navegación y acciones de editar y duplicar
    viewRoutineDetail(routine: Rutina): void {
        this.router.navigate(['/rutina-detalle', routine.id]);
    }

    editRoutine(routine: Rutina): void {
        this.router.navigate(['/trainer/edit-routine', routine.id]);
    }

    duplicateRoutine(routine: Rutina): void {
        this.router.navigate(['/trainer/create-routine'], {
            queryParams: {
                duplicate: routine.id,
                clientId: routine.usuarioId
            }
        });
    }

    openDeleteModal(routine: Rutina): void {
        this.selectedRoutine = routine;
        this.showDeleteModal = true;
    }

    closeDeleteModal(): void {
        this.showDeleteModal = false;
        this.selectedRoutine = null;
    }

    confirmDelete(): void {
        if (this.selectedRoutine) {
            console.log('Eliminando rutina:', this.selectedRoutine.id);
            this.rutinaService.deleteRoutine(this.selectedRoutine.id).subscribe({
                next: () => {
                  this.loadData();
                  this.closeDeleteModal();
                },
                error: (error) => {
                  console.error('Error deleting routine:', error);
                }
            });
        }
    }

    createNewRoutine(): void {
        this.router.navigate(['/trainer/create-routine']);
      }
    
      viewClientRoutines(clientId: number): void {
        this.filterByClient = clientId.toString();
        this.applyFilters();
      }
    
      goBackToDashboard(): void {
        this.router.navigate(['/trainer-dashboard']);
      }
    
      // Limpiar filtros
      clearFilters(): void {
        this.searchTerm = '';
        this.filterByClient = this.specificClientId ? this.specificClientId.toString() : '';
        this.filterByType = '';
        this.filterByCategory = '';
        this.filterByStatus = '';
        this.applyFilters();
      }
    
      // Estadísticas rápidas
      getTotalRoutines(): number {
        return this.allRoutines.length;
      }
    
      getCompletedRoutines(): number {
        return this.allRoutines.filter(r => this.getRoutineStatus(r) === 'completed').length;
      }
    
      getPendingRoutines(): number {
        return this.allRoutines.filter(r => this.getRoutineStatus(r) === 'pending').length;
      }
    
      getActiveRoutines(): number {
        return this.allRoutines.filter(r => this.getRoutineStatus(r) === 'active').length;
      }
    
      // Formateo de fecha
      formatDate(date: Date | string | undefined): string {
        if (!date) return 'No registrado';
        try {
          const d = new Date(date);
          if (isNaN(d.getTime())) return 'Fecha inválida';
          return d.toLocaleDateString('es-ES', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric'
          });
        } catch {
          return 'Fecha inválida';
        }
      }
    
      // Obtener feedback de la rutina
      getRoutineFeedback(routine: Rutina): string {
        if (routine.resultado_entreno?.comentarios) {
          return routine.resultado_entreno.comentarios;
        }
        return 'Sin comentarios';
      }
    
      // Verificar si hay ejercicios
      hasExercises(routine: Rutina): boolean {
        return routine.rutinaEjercicios && routine.rutinaEjercicios.length > 0;
      }
    
      getExerciseCount(routine: Rutina): number {
        return routine.rutinaEjercicios?.length || 0;
      }
}