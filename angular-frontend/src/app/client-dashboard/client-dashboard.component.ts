import { Component, OnInit } from '@angular/core';
import { AuthService } from '../services/auth.service';
import { Rutina, RutinaService, ResultadoEntreno} from '../services/rutina.service';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { forkJoin } from 'rxjs';

@Component({
  selector: 'app-client-dashboard',
  templateUrl: './client-dashboard.component.html',
  standalone: true,
  imports: [CommonModule, FormsModule]
})
export class ClientDashboardComponent implements OnInit{
  currentUser: any;
  assignedRoutines: Rutina[] = [];
  workoutResults: ResultadoEntreno[] = [];
  showFeedbackModal: boolean = false;
  selectedRoutine: Rutina | null = null;
  loading = false;
  difficultyLevels = ['Muy Fácil', 'Fácil', 'Moderado', 'Difícil', 'Muy Difícil'];

  feedback: Partial<ResultadoEntreno> = {
    dificultad_percibida: 0,
    comentarios: '',
    duracion_minutos: 0,
  };

  constructor(
    private authService: AuthService,
    private rutinaService: RutinaService
  ) {}

  ngOnInit(): void {
    this.currentUser = this.authService.getCurrentUser();
    if (this.currentUser && this.currentUser.nombre) {
      this.loadUserData();
    }
  }

  loadUserData(): void {
    if (!this.currentUser || !this.currentUser.nombre) {
      console.error('Usuario no válido para cargar datos');
      return;
    }
    this.loading = true;

    forkJoin({
      rutinas: this.rutinaService.getUserRoutineByName(this.currentUser.nombre),
      resultados: this.rutinaService.getWorkoutResultsByUser(this.currentUser.nombre)
    }).subscribe({
      next: (data) => {
        this.assignedRoutines = data.rutinas || [];
        this.workoutResults = data.resultados || [];;

        // Combinación de rutinas con resultados
        this.assignedRoutines.forEach(rutina => {
          const resultado = this.workoutResults.find(r => r.rutina_id === rutina.id);
          if (resultado) {
            rutina.resultado_entreno = resultado;
          }
        });
        this.loading = false;
      },
      error: (error) => {
        console.error('Error al cargar los datos del usuario:', error);
        this.loading = false;
        this.loadRoutinesOnly();
      }
    });
  }

  loadRoutinesOnly(): void {
    this.rutinaService.getUserRoutineByName(this.currentUser.nombre).subscribe({
      next: (rutinas) => {
        this.assignedRoutines = rutinas;
        this.loading = false;
      },
      error: (error) => {
        console.error('Error al cargar las rutinas del usuario:', error);
        this.loading = false;
      }
    });
  }

  getStatusClass(hasResult: boolean, isCompleted: boolean): string {
    if (!hasResult) {
      return 'bg-yellow-100 text-yellow-800';
    }
    return isCompleted ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800';
  }

  getStatusText(hasResult: boolean, isCompleted: boolean): string {
    if(!hasResult) {
      return 'Pendiente';
    }
    return isCompleted ? 'Completado' : 'En Progreso';
  }

  markAsCompleted(rutina: Rutina): void {
    if (!this.currentUser || !this.currentUser.id || !rutina || !rutina.id) {
      console.error('Datos insuficientes para marcar rutina como completada');
      alert('Error: Datos de usuario o rutina no válidos');
      return;
    }
    const resultado: Partial<ResultadoEntreno> = {
      usuario_id: this.currentUser.id,
      rutina_id: rutina.id,
      fecha: new Date(),
      completado: true,
      duracion_minutos: 30,
      dificultad_percibida: 3,
      comentarios: ''
    };

    this.rutinaService.createWorkoutResult(resultado).subscribe({
      next: (resultado) => {
        rutina.resultado_entreno = resultado;
        console.log('Rutina marcada como completada');
        this.openFeedbackModal(rutina);
      },
      error: (error) => {
        console.error('Error al marcar la rutina como completada', error);
        alert('Error al marcar la rutina como completada. Por favor, inténtalo de nuevo');
      }
    });
  }

  openFeedbackModal(rutina:Rutina): void {
    this.selectedRoutine = rutina;
    this.showFeedbackModal = true;

    // Cargar resultados si ya existen
    if(rutina.resultado_entreno) {
      this.feedback = {
        id: rutina.resultado_entreno.id,
        rutina_id: rutina.id,
        usuario_id: this.currentUser.id,
        dificultad_percibida: rutina.resultado_entreno.dificultad_percibida,
        comentarios: rutina.resultado_entreno.comentarios,
        duracion_minutos: rutina.resultado_entreno.duracion_minutos,
        completado: rutina.resultado_entreno.completado, 
        fecha: rutina.resultado_entreno.fecha
      };
    } else {
      this.feedback = {
        rutina_id: rutina.id, 
        usuario_id: this.currentUser.id,
        dificultad_percibida: 3,
        comentarios: '',
        duracion_minutos: 30,
        completado: true,
        fecha: new Date()
      };
    }
  }

  closeFeedbackModal(): void {
    this.showFeedbackModal = false;
    this.selectedRoutine = null;
    this.feedback = {
      dificultad_percibida: 0,
      comentarios: '',
      duracion_minutos: 0,
    };
  }

  setDifficulty(level: number): void {
    this.feedback.dificultad_percibida = level;
  }

  submitFeedback(): void {
    if (!this.feedback.dificultad_percibida || !this.feedback.duracion_minutos) {
      alert('Por favor, completa todos los campos requeridos.');
      return;
    }
    this.feedback.fecha = new Date();
    this.feedback.completado = true;

    if(this.feedback.id) {
      // Actualizar feedback existente
      this.rutinaService.updateWorkoutResult(this.feedback.id, this.feedback).subscribe({
        next: (resultado) => {
          if(this.selectedRoutine) {
          this.selectedRoutine.resultado_entreno = resultado;
        }
        this.closeFeedbackModal();
        alert('¡Feedback actualizado correctamente!');
      },
      error: (error) => {
        console.error('Error al actualizar el feedback', error);
        alert('Error al actualizar el feedback. Por favor, inténtalo de nuevo');
      }
      });
    } else {
      // Crear nuevo feedback
      this.rutinaService.createWorkoutResult(this.feedback).subscribe({
        next: (resultado) => {
          if(this.selectedRoutine) {
            this.selectedRoutine.resultado_entreno = resultado;
          }
          this.closeFeedbackModal();
          alert('¡Feedback actualizado correctamente!');
        },
        error: (error) => {
          console.error('Error al actualizar el feedback', error);
          alert('Error al actualizar el feedback. Por favor, inténtalo de nuevo');
        }
      });
    }
  }

  formatTime(seconds: number | undefined): string {
    if(!seconds || seconds === 0) return '0:00';
    const mins = Math.floor(seconds / 60);
    const secs = seconds % 60;
    return `${mins}:${secs.toString().padStart(2, '0')}`;
  }

  formatDate(date: Date | string | undefined): string {
    try {
      const d = new Date(date);
      if (isNaN(d.getTime())) return '';
      return d.toLocaleDateString('es-ES', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
      });
    } catch (error) {
      return '';
    }
  }
  
  logout(): void {
    this.authService.logout();
  }
}