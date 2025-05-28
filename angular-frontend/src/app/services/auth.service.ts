import { Injectable } from "@angular/core";
import { HttpClient } from "@angular/common/http";
import { BehaviorSubject, Observable } from "rxjs";
import { tap } from "rxjs";
import { Router } from "@angular/router";

export interface User {
    id: number;
    nombre: string;
    apellidos: string;
    email: string;
    type: 'client' | 'trainer';
    entrenador_id?: number;
    especialidad?: string;
    clientes_activos?: number;
}

interface LoginResponse {
    message: string;
    user: User;
    token: string;
}

@Injectable({
    providedIn: 'root'
})
export class AuthService {
    private currentUserSubject = new BehaviorSubject<User | null>(null);
    public currentUser$ = this.currentUserSubject.asObservable();
    private apiUrl = 'http://localhost:8000/api';

    constructor(private http: HttpClient, private router: Router) {
        const savedUser = localStorage.getItem('currentUser');
        if (savedUser) {
          this.currentUserSubject.next(JSON.parse(savedUser));
        }
      }
    
      login(credentials: any): Observable<LoginResponse> {
        return this.http.post<LoginResponse>(`${this.apiUrl}/login`, credentials).pipe(
          tap(response => {
            if (response.user && response.token) {
              localStorage.setItem('token', response.token);
              localStorage.setItem('currentUser', JSON.stringify(response.user));
              this.currentUserSubject.next(response.user);
              this.redirectByRole();
            }
          })
        );
      }
    
      logout(): void {
        localStorage.removeItem('token');
        localStorage.removeItem('currentUser');
        this.currentUserSubject.next(null);
        this.router.navigate(['/login']);
      }
    
      isLoggedIn(): boolean {
        return !!localStorage.getItem('token');
      }
    
      getCurrentUser(): User | null {
        return this.currentUserSubject.value;
      }
    
      getUserRole(): string {
        const user = this.getCurrentUser();
        if (!user) return '';
        return user.type === 'trainer' ? 'TRAINER' : 'CLIENT';
      }
    
      isTrainer(): boolean {
        return this.getUserRole() === 'TRAINER';
      }
    
      isClient(): boolean {
        return this.getUserRole() === 'CLIENT';
      }
    
      private redirectByRole(): void {
        const role = this.getUserRole();
        if (role === 'TRAINER') {
          this.router.navigate(['/trainer-dashboard']);
        } else if (role === 'CLIENT') {
          this.router.navigate(['/client-dashboard']);
        }
    }
}