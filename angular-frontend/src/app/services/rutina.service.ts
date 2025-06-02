import { Injectable } from "@angular/core";
import { HttpClient } from "@angular/common/http";
import { Observable } from "rxjs";
import { map } from "rxjs/operators";

export interface Ejercicio {
    id: number;
    nombre: string;
    descripcion: string;
    dificultad: string;
    categoria: string;
}

export interface RutinaEjercicio {
    id: number;
    rutina_id: number;
    ejercicio_id: number;
    nombre: string;
    series: number;
    repeticiones: number;
    descanso_segundos: number;
    orden: number;
    notas?: string;
    ejercicio: Ejercicio;
}

export interface Rutina {
    id: number;
    entrenador_id: number;
    usuario_id: number;
    nombre: string;
    tipo_rutina: string;
    series: number;
    categoria: string;
    descripcion: string;
    rutinaEjercicios: RutinaEjercicio[];
    resultado_entreno?: ResultadoEntreno;
}

export interface ResultadoEntreno {
    id: number;
    usuario_id: number;
    rutina_id: number;
    fecha: Date;
    duracion_minutos: number;
    dificultad_percibida: number;
    comentarios: string;
    completado: boolean;
}

@Injectable({
    providedIn: 'root'
})
export class RutinaService {
    private apiUrl = 'http://localhost:8000/api';

    constructor(private http: HttpClient) {}

    // Obtención de las rutinas por usuario
    getUserRoutineByName(nombreUsuario: string): Observable<Rutina[]> {
        return this.http.get<Rutina[]>(`${this.apiUrl}/rutinas/buscar/usuario/${nombreUsuario}`);
    }

    // Obtención de las rutinas por ID
    getRoutineById(rutinaId: number): Observable<Rutina> {
        return this.http.get<Rutina>(`${this.apiUrl}/rutinas/${rutinaId}`);
    }

    // Obtener las rutinas con los ejercicios
    getRutinaById(rutinaId: number): Observable<Rutina> {
        return this.http.get<Rutina>(`${this.apiUrl}/rutinas/${rutinaId}`);
    }

    // Obtener las rutinas por entrenador
    getRoutinesByTrainer(trainerId: number): Observable<Rutina[]> {
        return this.http.get<Rutina[]>(`${this.apiUrl}/rutinas/buscar/entrenador/${trainerId}`);
    }

    // Crear resultado de entrenamiento
    createWorkoutResult(resultado: Partial<ResultadoEntreno>): Observable<ResultadoEntreno> {
        return this.http.post<ResultadoEntreno>(`${this.apiUrl}/resultado-entrenos`, resultado);
    }

    // Actualizar resultado de entrenamiento
    updateWorkoutResult(resultadoId: number, resultado: Partial<ResultadoEntreno>): Observable<ResultadoEntreno> {
        return this.http.put<ResultadoEntreno>(`${this.apiUrl}/resultado-entrenos/${resultadoId}`, resultado);
    }

    // Obtener resultados de entrenamiento por usuario
    getWorkoutResultsByUser(nombreUsuario: string): Observable<ResultadoEntreno[]> {
        return this.http.get<ResultadoEntreno[]>(`${this.apiUrl}/resultado-entrenos/usuario/nombre/${nombreUsuario}`);
    }

    // Obtener resultados por ID
    getWorkoutResultById(resultadoId: number): Observable<ResultadoEntreno> {
        return this.http.get<ResultadoEntreno>(`${this.apiUrl}/resultado-entrenos/${resultadoId}`);
    }

    // Crear rutinas con ejercicios
    createRoutineWithExercises(routineData: any, exercises: any[]): Observable<any>{
        return this.http.post<any>(`${this.apiUrl}/rutinas`, {
            ...routineData,
            ejercicios: exercises
        });
    }

    // Actualizar rutina
    updateRoutine(rutinaId: number, routineData: any): Observable<Rutina> {
        return this.http.put<Rutina>(`${this.apiUrl}/rutinas/${rutinaId}`, routineData);
    }

    // Eliminar rutina
    deleteRoutine(rutinaId: number): Observable<void> {
        return this.http.delete<void>(`${this.apiUrl}/rutinas/${rutinaId}`);
    }
}