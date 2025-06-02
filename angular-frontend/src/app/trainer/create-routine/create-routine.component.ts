import { Component, OnInit } from "@angular/core";
import { Router, ActivatedRoute} from "@angular/router";
import { CommonModule } from "@angular/common";
import { FormsModule } from "@angular/forms";
import { AuthService } from "../../services/auth.service";
import { TrainerService, Usuario } from "../../services/trainer.service";
import { RutinaService } from "../../services/rutina.service";

interface ExerciseForm {
    ejercicio_id?: number;
    nombre: string;
    series: number;
    repeticiones: number;
    descanso_segundos: number;
    orden: number;
    notas?: string;
// Para ejercicios personalizados
    descripcion?: string;
    dificultad?: string;
    categoria?: string;
}

interface RoutineForm {
    nombre: string;
    tipo_rutina: string;
    categoria: string;
    descripcion: string;
    series: number;
    usuario_id: number;
    ejercicios: ExerciseForm[];
}

@Component({
    selector: 'app-create-routine',
    standalone: true,
    imports: [CommonModule, FormsModule],
    templateUrl: './create-routine.component.html'
})
export class CreateRoutineComponent implements OnInit {
    currentTrainer: any;
    selectedClient: Usuario | null = null;
    myClients: Usuario[] = [];

    loading = false;
    saving = false;
    error: string | null = null;
    success: string | null = null;

    // Formulario base para las rutinas
    routineForm: RoutineForm = {
        nombre: '',
        tipo_rutina: '',
        categoria: '',
        descripcion: '',
        series: 1,
        usuario_id: 0,
        ejercicios: []
    };

    // Opciones para el Select
    tiposRutina = ['Fuerza', 'Resistencia', 'Funcional', 'Rehabilitación', 'Cardio', 'Flexibilidad'];
    categorias = ['Principiante', 'Intermedio', 'Avanzado', 'Terapéutica'];
    dificultades = ['Fácil', 'Intermedio', 'Difícil'];
    categoriasEjercicio = ['Fuerza', 'Cardio', 'Flexibilidad', 'Funcional', 'Rehabilitación'];

    // Control del formulario
    currentStep = 1;
    totalSteps= 3;

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
            const clientId = params['clientId'];
            if (clientId) {
                this.routineForm.usuario_id = parseInt(clientId);
            }
        });
        this.loadMyClients();
    }

    loadMyClients(): void {
        this.loading = true;
        this.trainerService.getTrainerById(this.currentTrainer.id).subscribe({
            next: (trainer) => {
                const trainerData = Array.isArray(trainer) ? trainer[0] : trainer;
                this.myClients = trainerData?.usuarios || [];
            if (this.routineForm.usuario_id > 0) {
                this.selectedClient = this.myClients.find(c => c.id === this.routineForm.usuario_id) || null;
            }
            this.loading = false;
            },
            error: (error) => {
                console.error('Error loading clients:', error);
                this.error = 'Error al cargar los clientes';
                this.loading = false;
            }
        });
    }

    // Navegación entre pasos
    nextStep(): void {
        if (this.validateCurrentStep()) {
            this.currentStep++;
        }
    }

    prevStep(): void {
        this.currentStep--;
    }

    goToStep(step: number): void {
        this.currentStep = step;
    }

    validateCurrentStep(): boolean {
        switch (this.currentStep) {
            case 1:
                return this.validateBasicInfo();
            case 2: 
            return this.validateExercises();
            case 3:
                return true;
            default:
                return false;
        }
    }

    validateBasicInfo(): boolean {
        if (!this.routineForm.nombre.trim()) {
            this.error = 'El nombre de la rutina es obligatorio';
            return false;
        }
        if(!this.routineForm.tipo_rutina) {
            this.error = 'Debe seleccionar un tipo de rutina';
            return false;
        }
        if (!this.routineForm.categoria) {
            this.error = 'Debe seleccionar una categoría';
            return false;
        }
        if (this.routineForm.usuario_id === 0) {
            this.error = 'Debe seleccionar un cliente';
            return false;
        }
        this.error = null;
        return true;
    }  

    validateExercises(): boolean {
        if (this.routineForm.ejercicios.length === 0) {
            this.error = 'Debes añadir al menos un ejercicio';
            return false;
        }

        for (let exercises of this.routineForm.ejercicios) {
            if(!exercises.nombre.trim()) {
                this.error = 'Todos los ejercicios deben tener un nombre';
                return false;
            }
            if (exercises.series <= 0 || exercises.repeticiones <= 0) {
                this.error = 'Series y repeticiones deben ser mayor a 0';
                return false;
            }
        }

        this.error = null;
        return true;
    }

    // Gestión de los ejercicios
    addExercise(): void {
        const newExercise: ExerciseForm = {
            nombre: '',
            series: 3,
            repeticiones: 10,
            descanso_segundos: 60,
            orden: this.routineForm.ejercicios.length + 1,
            notas: '',
            descripcion: '',
            dificultad: 'Intermedio',
            categoria: 'Fuerza'
        };
        this.routineForm.ejercicios.push(newExercise);
    }

    removeExercise(index: number): void {
        this.routineForm.ejercicios.splice(index, 1);
        this.routineForm.ejercicios.forEach((exercise, i) => {
            exercise.orden = i + 1;
        });
    }

    moveExerciseUp(index: number): void {
        if (index > 0) {
            const temp = this.routineForm.ejercicios[index];
            this.routineForm.ejercicios[index] = this.routineForm.ejercicios[index - 1];
            this.routineForm.ejercicios[index - 1] = temp;
            // Actualizar orden
            this.routineForm.ejercicios.forEach((exercise, i) => {
                exercise.orden = i + 1;
            });
        }
    }

    moveExerciseDown(index: number): void {
        if (index < this.routineForm.ejercicios.length - 1) {
            const temp = this.routineForm.ejercicios[index];
            this.routineForm.ejercicios[index] = this.routineForm.ejercicios[index + 1];
            this.routineForm.ejercicios[index + 1] = temp;
            // Actualizar orden
            this.routineForm.ejercicios.forEach((exercise, i) => {
                exercise.orden = i + 1;
            });
        }
    }

    // Gestión de clientes 
    onClientChange(): void {
        this.selectedClient = this.myClients.find(c => c.id === this.routineForm.usuario_id) || null;
    }

    // Formateo del tiempo 
    formatTimeDisplay(seconds: number): string {
        const mins = Math.floor(seconds / 60);
        const secs = seconds % 60;
        return mins > 0 ? `${mins}:${secs.toString().padStart(2, '0')}`: `${secs}s`;
    }

    // Calcular el tiempo estimado
    getTotalEstimatedTime(): number {
        return this.routineForm.ejercicios.reduce((total, exercise) => {
            // 30 segundos por series más tiempo de descanso
            const exerciseTime = (exercise.series * 30) + (exercise.series * exercise.descanso_segundos);
            return total + exerciseTime;
        }, 0);
    }

    // Calcular total de series
    getTotalSeries(): number {
        return this.routineForm.ejercicios.reduce((total, exercise) => total + exercise.series, 0);
    }

    // Calcular promedio descanso
    getAverageRest(): string {
        if (this.routineForm.ejercicios.length === 0) return '0s';

        const totalRest = this.routineForm.ejercicios.reduce((total, exercise) => total + exercise.descanso_segundos, 0);
        const average = totalRest / this.routineForm.ejercicios.length;
        return this.formatTimeDisplay(average);
    }

    // Guardar rutina
    saveRoutine(): void {
        if (!this.validateCurrentStep()) {
            return;
        }
            this.saving = true;
            this.error = null;

            const routineData = {
                nombre: this.routineForm.nombre,
                tipo_rutina: this.routineForm.tipo_rutina,
                categoria: this.routineForm.categoria,
                descripcion: this.routineForm.descripcion,
                series: this.routineForm.series,
                usuario_id: this.routineForm.usuario_id,
                entrenador_id: this.currentTrainer.id
            };

        this.rutinaService.createRoutineWithExercises(routineData, this.routineForm.ejercicios).subscribe({
            next: (response) => {
                this.saving = false;
                this.success = 'Rutina creada exitosamente';
                setTimeout(() => {
                    this.router.navigate(['/trainer/routines']);
                }, 2000);
            },
            error: (error) => {
                console.error('Error creating routine:', error);
                this.error = 'Error al crear la rutina';
                this.saving = false;
            }
        });
    }

    // Navegación a otras rutas
    goBackToDashboard(): void {
        this.router.navigate(['/trainer-dashboard']);
    }

    goToClient(): void {
        this.router.navigate(['/trainer/clients']);
    }

    // Reseteo de formulario
    resetForm(): void {
        this.routineForm = {
            nombre: '',
            tipo_rutina: '',
            categoria: '',
            descripcion: '',
            series: 1,
            usuario_id: 0,
            ejercicios: []
        };
        this.selectedClient = null;
        this.currentStep = 1;
        this.error = null;
        this.success = null;
    }
}