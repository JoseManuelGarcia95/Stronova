import { Injectable } from "@angular/core";
import { HttpClient } from "@angular/common/http";
import { Observable } from "rxjs";

export interface Entrenador {
    id: number;
    nombre: string;
    apellidos: string;
    email: string;
    especialidad?: string;
    clientes_activos: number;
    usuarios?: Usuario[];
    rutinasCreadas?: Rutina[];
}

export interface Usuario {
    id: number;
    nombre: string;
    apellidos: string;
    email: string;
    genero?: string;
    altura?: number;
    peso_inicial?: number;
    lesiones?: string;
    objetivo?: string;
    entrenador_id?: number;
    entrenador?: Entrenador;
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
    rutinaEjercicios?: any[];
    resultado_entreno: any;
}

@Injectable({
    providedIn: 'root'
})
export class TrainerService {
    private apiUrl = 'http://localhost:8000/api';

    constructor(private http: HttpClient) {}

    // Obtener todos los entrenadores
    getAllTrainers(): Observable<Entrenador[]> {
        return this.http.get<Entrenador[]>(`${this.apiUrl}/entrenadores`);
    }

    // Obtener entrenador por ID
    getTrainerById(trainerId: number): Observable<Entrenador[]> {
        return this.http.get<Entrenador[]>(`${this.apiUrl}/entrenadores/${trainerId}`);
    }

    // Obtener usuarios sin un entrenador asignado
    getUnassignedUsers(): Observable<Usuario[]> {
        return this.http.get<Usuario[]>(`${this.apiUrl}/entrenadores/usuarios-sin-asignar`);
    }

    // Asignar usuario a entrenador
    assignUserToTrainer(trainerId: number, userId: number): Observable<Entrenador> {
        return this.http.put<Entrenador>(`${this.apiUrl}/entrenadores/${trainerId}/asignar-usuario/${userId}`, {});
    }

    // Desasignar usuario de entrenador
    unassignUserFromTrainer(trainerId: number, userId: number): Observable<Entrenador> {
        return this.http.put<Entrenador>(`${this.apiUrl}/entrenadores/${trainerId}/desasignar-usuario/${userId}`, {});
    }

    // Buscar entrenadores por especialidad
    getTrainersBySpecialty(especialidad: string): Observable<Entrenador[]> {
        return this.http.get<Entrenador[]>(`${this.apiUrl}/entrenadores/buscar/especialidad/${especialidad}`);
    }

    // Buscar entrenadores por apellidos
    getTrainerByLastName(apellidos: string): Observable<Entrenador[]> {
        return this.http.get<Entrenador[]>(`${this.apiUrl}/entrenadores/buscar/apellidos/${apellidos}`);
    }

    // Crear nuevo entrenador
    createTrainer(trainerData: Partial<Entrenador>): Observable<Entrenador> {
        return this.http.post<Entrenador>(`${this.apiUrl}/entrenadores`, trainerData);
    }

    // Actualizar entrenador
    updateTrainer(trainerId: number, trainerData: Partial<Entrenador>): Observable<Entrenador> {
        return this.http.put<Entrenador>(`${this.apiUrl}/entrenadores/${trainerId}`, trainerData);
    }

    // Eliminar entrenador
    deleteTrainer(trainerId: number): Observable<void> {
        return this.http.delete<void>(`${this.apiUrl}/entrenadores/${trainerId}`);
    }
}