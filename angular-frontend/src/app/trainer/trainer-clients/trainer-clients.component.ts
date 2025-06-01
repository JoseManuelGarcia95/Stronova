import { Component, OnInit } from "@angular/core";
import { Router } from "@angular/router";
import { CommonModule } from "@angular/common";
import { FormsModule } from "@angular/forms";
import { AuthService } from "../../services/auth.service";
import { TrainerService, Usuario, Entrenador } from "../../services/trainer.service";
import { forkJoin } from "rxjs";

@Component ({
    selector: 'app-trainer-clients',
    standalone: true,
    imports: [CommonModule, FormsModule],
    templateUrl: './trainer-clients.component.html'
})
export class TrainerClientsComponent implements OnInit{
    currentTrainer: any;
    myClients: Usuario[] = [];
    unassignedUsers: Usuario[] = [];
    loading = true;
    error: string | null = null; 

    // Modales
    showAssignModal = false;
    showClientDetailModal = false;
    selectedClient: Usuario | null = null;

    // Filtros y búsquedas
    searchTerm = '';
    filterByObjective = '';
    filterByGender = '';
    availableObjectives = ['Pérdida de peso', 'Ganancia muscular', 'Resistencia', 'Rehabilitación', 'Fuerza']; 

    constructor(
        private authService: AuthService,
        private trainerService: TrainerService,
        private router: Router
    ) {}

    ngOnInit(): void {
    console.log('🧑‍💼 Current user:', this.authService.getCurrentUser());
    console.log('🎫 Token from service:', this.authService.getToken());
    console.log('🎫 Token from localStorage:', localStorage.getItem('token'));
    console.log('✅ Is logged in:', this.authService.isLoggedIn());

        this.currentTrainer = this.authService.getCurrentUser();
        if (this.currentTrainer?.id) {
            console.log('👨‍🏫 Trainer ID:', this.currentTrainer.id);
            this.loadClientsData();
        } else {
            this.error = 'No se pudo cargar la información del entrenador';
            this.loading = false;
        }
    }

    loadClientsData(): void {
        this.loading = true;
        this.error = null;

        forkJoin({
            trainer: this.trainerService.getTrainerById(this.currentTrainer.id),
            unassigned: this.trainerService.getUnassignedUsers()
        }).subscribe({
            next: (data) => {
                const trainer = Array.isArray(data.trainer) ? data.trainer[0] : data.trainer;
                this.myClients = trainer.usuarios || [];
                this.unassignedUsers = data.unassigned || [];
                this.loading = false;
            },
            error: (error) => {
                console.error('Error loading data:', error);
                this.error = 'Error al cargar los datos del cliente';
                this.loading = false;
            }
        });
    }

    // Filtros y búsquedas
    get filteredClients(): Usuario[] {
        return this.myClients.filter(client => {
            const matchesSearch = !this.searchTerm || 
                client.nombre.toLowerCase().includes(this.searchTerm.toLowerCase()) ||
                client.apellidos.toLowerCase().includes(this.searchTerm.toLowerCase()) ||
                client.email.toLowerCase().includes(this.searchTerm.toLowerCase());

            const matchesObjective = !this.filterByObjective ||
                client.objetivo === this.filterByObjective;

            const matchesGender = !this.filterByGender ||
                client.genero === this.filterByGender;

            return matchesSearch && matchesObjective && matchesGender;
        });
    }

    get filteredUnassignedUsers(): Usuario[] {
        return this.unassignedUsers.filter(user => {
            const matchesSearch = !this.searchTerm ||
                user.nombre.toLowerCase().includes(this.searchTerm.toLowerCase()) || 
                user.apellidos.toLowerCase().includes(this.searchTerm.toLowerCase()) || 
                user.email.toLowerCase().includes(this.searchTerm.toLowerCase());
            return matchesSearch;
        });
    }

    // Gestión de clientes
    assignClient(userId: number): void {
        if (!this.currentTrainer?.id) return;

        this.trainerService.assignUserToTrainer(this.currentTrainer.id, userId).subscribe({
            next:() => {
                this.loadClientsData();
                this.closeAssignModal();
            },
            error: (error) => {
                console.error('Error assigning client', error);
                alert('Error al asignar cliente');
            }
        });
    }

    unassignClient(userId: number): void {
        if (!this.currentTrainer?.id) return;

        if (confirm('¿Estás seguro de que quieres desasignar este cliente?')) {
            this.trainerService.unassignUserFromTrainer(this.currentTrainer.id, userId).subscribe({
                next: () => {
                    this.loadClientsData();
                },
                error: (error) => {
                    console.error('Error unassigning client', error);
                    alert('Error al desasignar cliente');
                }
            });
        }
    }

    // Navegación entre rutas
    createRoutineForClient(client: Usuario): void {
        this.router.navigate(['/trainer/create-routine'], {
            queryParams: { clientId: client.id}
        });
    }

    viewClientRoutines(client: Usuario): void {
        this.router.navigate(['/trainer/routines'], {
            queryParams: { clientId: client.id}
        });
    }

    goBackToDashboard(): void {
        this.router.navigate(['/trainer-dashboard']);
    }

    // Funciones de los modales
    openAssignModal(): void {
        this.showAssignModal = true;
    }

    closeAssignModal(): void {
        this.showAssignModal = false;
        this.searchTerm = '';
    }

    openClientDetail(client: Usuario): void {
        this.selectedClient = client;
        this.showClientDetailModal = true;
    }

    closeClientDetail(): void {
        this.showClientDetailModal = false;
        this.selectedClient = null;
    }

    // Funciones de utilidad
    getClientStatus(client: Usuario): string {
        return 'Activo';
    }

    getClientStatusClass(status: string): string {
        switch (status.toLowerCase()) {
            case 'activo':
                return 'bg-green-100 text-green-800';
            case 'inactivo':
                return 'bg-red-100 text-red-800';
            case 'nuevo':
                return 'bg-blue-100 text-blue-800';
            default:
                return 'bg-gray-100 text-gray-800';
        }
    }

    getBMI(client: Usuario): string {
        if(client.peso_inicial && client.altura) {
            const heightInMeters = client.altura / 100;
            const bmi = client.peso_inicial / (heightInMeters * heightInMeters);
            return bmi.toFixed(1);
        }
        return 'N/A';
    }

    getBMIClass(bmi: string): string {
        if (bmi === 'N/A') return 'text-gray-500';

        const bmiValue = parseFloat(bmi);
        if (bmiValue < 18.5) return 'text-blue-600';
        if (bmiValue < 25) return 'text-green-600';
        if (bmiValue < 30) return 'text-yellow-600';
        return 'text-red-600';
    }

    // Limpiar filtros de búsqueda
    clearFilters(): void {
        this.searchTerm = '';
        this.filterByObjective = '';
        this.filterByGender = '';
    }
}