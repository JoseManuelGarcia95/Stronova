import { Injectable } from "@angular/core";
import { ActivatedRouteSnapshot, CanActivate, GuardResult, MaybeAsync, Router, RouterStateSnapshot } from "@angular/router";
import { AuthService } from "../services/auth.service";

@Injectable({
    providedIn: 'root'
})
export class TrainerGuard implements CanActivate {
    constructor(private authService: AuthService, private router: Router) {}

    canActivate(): boolean {
        if (this.authService.isLoggedIn() && this.authService.isTrainer()) {
            return true;
        }

        if (this.authService.isLoggedIn()) {
            this.router.navigate(['/client-dashboard']);
        } else {
            this.router.navigate(['/login']);
        }
        return false;
    }
}