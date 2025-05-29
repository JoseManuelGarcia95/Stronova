import { Component, OnInit } from "@angular/core";
import { ActivatedRoute, Router } from "@angular/router";
import { CommonModule } from "@angular/common";
import { Rutina, RutinaService } from "../../services/rutina.service";

@Component({
    selector: 'app-rutina-detalle',
    standalone: true,
    imports: [CommonModule],
    templateUrl: './rutina-detalle.component.html'
})
export class RutinaDetalleComponent implements OnInit {
    rutina: Rutina | null = null;
    loading: boolean = true;
    error: string | null = null;
    rutinaId: number | null = null;

    constructor(
        private route: ActivatedRoute,
        private router: Router,
        private rutinaService: RutinaService
    ) {}

    ngOnInit(): void {
        this.route.params.subscribe(params => {
            this.rutinaId = +params['id'];
            if (this.rutinaId) {
                this.cargarRutina();
            } else {
                this.error = 'Id de rutina no válido';
                this.loading = false;
            }
        });
    }

    cargarRutina(): void {
        if (!this.rutinaId) return;
        this.loading = true;
        this.error = null;

        this.rutinaService.getRoutineById(this.rutinaId).subscribe({
            next: (rutina) => {
                this.rutina = rutina;
                this.loading = false;
            },
            error: (error) => {
                console.error('Error al cargar la rutina:', error);
                this.error = 'Error al cargar la rutina. Intentalo de nuevo más tarde.';
                this.loading = false;
            }
        });
    }

    get ejerciciosOrdenados() {
        if (!this.rutina?.rutinaEjercicios) return [];
        return [...this.rutina.rutinaEjercicios].sort((a, b) => {
            const ordenA = a.orden || 999;
            const ordenB = b.orden || 999;
            return ordenA - ordenB;
        });
    }

    volver(): void {
        this.router.navigate(['/client-dashboard']);
    }

    formatTime(seconds: number | undefined): string {
        if (!seconds || seconds === 0) return '0:00';
        const mins = Math.floor(seconds / 60);
        const secs = seconds % 60;
        return `${mins}:${secs.toString().padStart(2, '0')}`;
    }

    getDificultadClass(dificultad: string): string {
        const dificultadLower = dificultad.toLowerCase();
        if (dificultadLower.includes('fácil') || dificultadLower.includes('facil')){
            return 'bg-green-100 text-green-800';
        } else if (dificultadLower.includes('intermedio') || dificultadLower.includes('medio')) {
            return 'bg-yellow-100 text-yellow-800';
        } else if (dificultadLower.includes('difícil') || dificultadLower.includes('dificil') || dificultadLower.includes('avanzado')) {
            return 'bg-red-100 text-red-800';
        }
        return 'bg-gray-100 text-gray-800';
    }

    getTotalSeries(): number {
        return this.ejerciciosOrdenados.reduce((total, ejercicio) => total + ejercicio.series, 0);
    }

    getTiempoEstimado(): string {
        const tiempoTotal = this.ejerciciosOrdenados.reduce((total, ejercicio) => {
            const tiempoEjercicio = (ejercicio.series * 30) + (ejercicio.series * (ejercicio.descanso_segundos || 0));
            return total + tiempoEjercicio;
        }, 0);
        const minutos = Math.round(tiempoTotal / 60);
        return `${minutos} min`;
    }

    getDificultadPromedio(): string {
        const ejerciciosConDificultad = this.ejerciciosOrdenados.filter(e => e.ejercicio?.dificultad);
        if (ejerciciosConDificultad.length === 0) return 'N/A';

        const dificultades = ejerciciosConDificultad.map(e => e.ejercicio!.dificultad);
        const dificultadMasComun = dificultades.reduce((a, b, i, arr) => 
            arr.filter(v => v === a).length >= arr.filter(v => v === b).length ? a : b
    );
    return dificultadMasComun;
    }
}