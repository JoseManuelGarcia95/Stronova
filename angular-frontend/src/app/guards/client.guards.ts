// src/app/guards/client.guard.ts
import { Injectable } from '@angular/core';
import { CanActivate, Router } from '@angular/router';
import { AuthService } from '../services/auth.service';

@Injectable({
  providedIn: 'root'
})
export class ClientGuard implements CanActivate {
  constructor(private authService: AuthService, private router: Router) {}

  canActivate(): boolean {
    if (this.authService.isLoggedIn() && this.authService.isClient()) {
      return true;
    }
    
    if (this.authService.isLoggedIn()) {
      this.router.navigate(['/trainer-dashboard']);
    } else {
      this.router.navigate(['/login']);
    }
    return false;
  }
}