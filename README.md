
⏰ Aujourd'hui: 28 Avril 2026, 14:00

1. Patient va sur http://localhost:4433
2. Se connecte: patient@medicline.local / Patient123!
3. Dropdown "Sélectionner une clinique" → "Clinique de Paris"
4. Voit les médecins: Dr. Laurent (Cardio), Dr. Sophie (Pédiatre)
5. Clic "Dr. Laurent"
6. Calendrier affiche:
   - Aujourd'hui 28/04: Créneaux 14:00-18:00 (car 14:00 actuellement, slots après OK)
   - Demain 29/04: Créneaux 09:00-12:00, 14:00-18:00
7. Clic "Demain 29/04 à 10:00" ✅
8. Formulaire:
   - Date: 2026-04-29
   - Heure: 10:00
   - Durée: 30 min
9. Clic "Confirmer" → Message: "Rendez-vous enregistré" ✅
10. RDV apparaît dans "Mes rendez-vous" avec 🟡 Pending

1. Admin se connecte: admin@medicline.local / Admin123!
2. Onglet "Rendez-vous"
3. Voit: Jean Dupont | Dr. Laurent | 29/04 10:00 | Pending 🟡
4. Clic → Change status à "confirmed"
5. Clic "Valider" → Statut devient 🟢 Confirmed
