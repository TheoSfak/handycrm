# Οδηγός Εφαρμογής "χωρίς ΦΠΑ"

## 📌 Στόχος
Να εμφανίζεται παντού στο HandyCRM ότι οι τιμές είναι **χωρίς ΦΠΑ**.

---

## ✅ Τι Έχει Γίνει (v1.3.7)

### 1. **Translations (el.json)**
Προστέθηκαν:
```json
"app": {
    "without_vat": "χωρίς ΦΠΑ",
    "without_vat_note": "Όλες οι τιμές είναι χωρίς ΦΠΑ"
}
```

### 2. **Helper Functions (config.php)**
Προστέθηκαν νέες συναρτήσεις:

```php
// Εμφανίζει τιμή με "(χωρίς ΦΠΑ)" σε μικρά γκρίζα γράμματα
formatCurrencyWithVAT($amount, $showNote = true)

// Προσθέτει "(χωρίς ΦΠΑ)" σε label
withoutVatLabel($label)
```

---

## 🎯 Πού να Εφαρμόσεις

### **A. Φόρμες Εισαγωγής/Επεξεργασίας**

#### 1. **Task Materials (views/projects/tasks/add.php & edit.php)**
```html
<!-- ΠΡΙΝ -->
<th>Τιμή Μονάδας</th>

<!-- ΜΕΤΑ -->
<th><?php echo withoutVatLabel('Τιμή Μονάδας'); ?></th>

<!-- Ή -->
<th>Τιμή Μονάδας <small class="text-muted">(χωρίς ΦΠΑ)</small></th>
```

#### 2. **Task Labor (views/projects/tasks/add.php & edit.php)**
```html
<!-- ΠΡΙΝ -->
<th>Ημερομίσθιο</th>

<!-- ΜΕΤΑ -->
<th><?php echo withoutVatLabel('Ημερομίσθιο'); ?></th>
```

#### 3. **Project Forms (views/projects/add.php & edit.php)**
```html
<!-- Κόστος Υλικών -->
<label><?php echo withoutVatLabel(__('projects.material_cost')); ?></label>

<!-- Κόστος Εργασίας -->
<label><?php echo withoutVatLabel(__('projects.labor_cost')); ?></label>
```

---

### **B. Λίστες/Πίνακες**

#### 1. **Project List (views/projects/index.php)**
```html
<!-- Column header για κόστος -->
<th><?php echo withoutVatLabel('Κόστος'); ?></th>

<!-- Εμφάνιση τιμής -->
<td><?php echo formatCurrencyWithVAT($project['total_cost']); ?></td>
```

#### 2. **Task Details (views/projects/show.php)**
```html
<!-- Στα materials -->
<th><?php echo withoutVatLabel('Τιμή'); ?></th>
<td><?php echo formatCurrencyWithVAT($material['unit_price']); ?></td>

<!-- Στα labor -->
<th><?php echo withoutVatLabel('Ημερομίσθιο'); ?></th>
<td><?php echo formatCurrencyWithVAT($labor['daily_rate']); ?></td>

<!-- Στα totals -->
<strong>Σύνολο:</strong> <?php echo formatCurrencyWithVAT($total); ?>
```

---

### **C. PDF Reports (controllers/ProjectReportController.php)**

#### Στο HTML του PDF:
```html
<!-- Header note -->
<div style="font-size: 10px; color: #666; text-align: right;">
    Όλες οι τιμές είναι χωρίς ΦΠΑ (<?php echo __('app.without_vat'); ?>)
</div>

<!-- Table headers -->
<th style="font-size: 10px; color: #666;">
    Τιμή Μονάδας<br>
    <span style="font-size: 8px;">(χωρίς ΦΠΑ)</span>
</th>

<!-- Total με σημείωση -->
<tr>
    <td colspan="3" style="text-align: right;"><strong>Σύνολο (χωρίς ΦΠΑ):</strong></td>
    <td style="text-align: right;"><strong><?php echo formatCurrency($total); ?></strong></td>
</tr>
```

---

### **D. Dashboard/Στατιστικά (views/dashboard/index.php)**

```html
<!-- Revenue card -->
<div class="card-body">
    <h5>Έσοδα Μήνα</h5>
    <h3><?php echo formatCurrencyWithVAT($revenue); ?></h3>
    <small class="text-muted">Όλα τα ποσά χωρίς ΦΠΑ</small>
</div>

<!-- Ή πιο subtle -->
<div class="card-footer text-muted">
    <small>* Τιμές χωρίς ΦΠΑ</small>
</div>
```

---

### **E. Materials Management (views/materials/)**

#### 1. **Material List (index.php)**
```html
<th><?php echo withoutVatLabel('Τιμή Αγοράς'); ?></th>
<th><?php echo withoutVatLabel('Τιμή Πώλησης'); ?></th>
```

#### 2. **Material Form (add.php & edit.php)**
```html
<label><?php echo withoutVatLabel('Τιμή Αγοράς'); ?></label>
<input type="number" name="purchase_price" step="0.01" />

<label><?php echo withoutVatLabel('Τιμή Πώλησης'); ?></label>
<input type="number" name="selling_price" step="0.01" />
```

---

## 📝 **Παραδείγματα Χρήσης**

### **Παράδειγμα 1: Inline στο HTML**
```html
<td>
    <strong>Σύνολο Υλικών:</strong> 
    <?php echo formatCurrency($materialsCost); ?> 
    <small class="text-muted">(χωρίς ΦΠΑ)</small>
</td>
```

### **Παράδειγμα 2: Με Helper Function**
```php
<td><?php echo formatCurrencyWithVAT($amount); ?></td>
```

### **Παράδειγμα 3: Label με σημείωση**
```php
<label><?php echo withoutVatLabel('Κόστος'); ?></label>
```

### **Παράδειγμα 4: PDF με global note**
```html
<div style="margin-bottom: 10px; padding: 5px; background: #f8f9fa; border-left: 3px solid #007bff;">
    <small><strong>Σημείωση:</strong> Όλες οι τιμές που εμφανίζονται είναι χωρίς ΦΠΑ 24%</small>
</div>
```

---

## 🎨 **CSS για Styling**

Πρόσθεσε στο `assets/css/style.css`:

```css
/* VAT note styling */
.vat-note {
    font-size: 0.75rem;
    color: #6c757d;
    font-weight: normal;
    font-style: italic;
}

.vat-note-inline {
    display: inline-block;
    margin-left: 5px;
}

/* Footer note for cards */
.card-footer.vat-disclaimer {
    background-color: #f8f9fa;
    border-top: 1px solid #dee2e6;
    font-size: 0.8rem;
    color: #6c757d;
}

/* PDF specific */
.pdf-vat-note {
    font-size: 8px;
    color: #666;
    font-style: italic;
}
```

---

## 🔄 **Migration για Υφιστάμενα Δεδομένα**

Τα υπάρχοντα δεδομένα είναι ΗΔΗ χωρίς ΦΠΑ, οπότε:
- ❌ **ΔΕΝ** χρειάζεται migration στη βάση
- ✅ Απλά προσθέτουμε την ένδειξη στο UI

---

## 📊 **Checklist Εφαρμογής**

### Views που πρέπει να αλλάξουν:

- [ ] `views/projects/index.php` - Project list
- [ ] `views/projects/show.php` - Project details
- [ ] `views/projects/add.php` - New project form
- [ ] `views/projects/edit.php` - Edit project form
- [ ] `views/projects/tasks/add.php` - New task form
- [ ] `views/projects/tasks/edit.php` - Edit task form
- [ ] `views/materials/index.php` - Materials list
- [ ] `views/materials/add.php` - New material form
- [ ] `views/materials/edit.php` - Edit material form
- [ ] `views/dashboard/index.php` - Dashboard stats
- [ ] `views/reports/index.php` - Reports page
- [ ] `controllers/ProjectReportController.php` - PDF reports

---

## 🚀 **Deployment Steps**

1. ✅ Ενημέρωσε το `languages/el.json` (ήδη έγινε)
2. ✅ Ενημέρωσε το `config/config.php` (ήδη έγινε)
3. ⚠️ Ενημέρωσε όλα τα views (δες checklist)
4. ⚠️ Πρόσθεσε CSS για styling
5. ⚠️ Test σε όλες τις σελίδες
6. ⚠️ Test PDF reports
7. ⚠️ Ανέβασε στο production

---

## 💡 **Best Practices**

### ✅ DO:
- Χρησιμοποίησε `formatCurrencyWithVAT()` για inline εμφάνιση
- Χρησιμοποίησε `withoutVatLabel()` για labels σε φόρμες
- Πρόσθεσε global note σε PDF reports
- Χρησιμοποίησε `<small class="text-muted">` για subtle appearance
- Κράτα το consistent σε όλο το app

### ❌ DON'T:
- Μην το γράφεις με ΚΕΦΑΛΑΙΑ (πολύ έντονο)
- Μην το βάζεις σε κάθε τιμή (δημιουργεί clutter)
- Μην ξεχνάς να το βάλεις στα PDF reports
- Μην αλλάζεις τα δεδομένα στη βάση (είναι ήδη χωρίς ΦΠΑ)

---

## 📱 **Responsive Design**

Για mobile:
```css
@media (max-width: 768px) {
    .vat-note {
        font-size: 0.65rem;
        display: block;
        margin-top: 2px;
    }
}
```

---

## 🎯 **Προτεινόμενη Στρατηγική**

### **Option 1: Subtle (Προτείνεται)**
- Global note στο header/footer κάθε σελίδας με τιμές
- Inline note μόνο σε headers (`<th>`)
- Όχι σε κάθε κελί

### **Option 2: Explicit**
- Inline note σε κάθε τιμή
- Χρήση `formatCurrencyWithVAT()` παντού

### **Option 3: Hybrid (Καλύτερη)**
- Global note στο top της σελίδας
- Inline note μόνο σε σημαντικά totals
- PDF: Global note + inline σε totals

---

## 📄 **Παράδειγμα Πλήρους Σελίδας**

```html
<!-- views/projects/show.php -->
<div class="container">
    <div class="alert alert-info">
        <small><i class="fas fa-info-circle"></i> Όλες οι τιμές εμφανίζονται χωρίς ΦΠΑ</small>
    </div>
    
    <div class="card">
        <div class="card-header">
            <h3><?php echo $project['title']; ?></h3>
        </div>
        
        <div class="card-body">
            <!-- Materials Table -->
            <h5>Υλικά</h5>
            <table class="table">
                <thead>
                    <tr>
                        <th>Υλικό</th>
                        <th>Ποσότητα</th>
                        <th>Τιμή Μονάδας <small class="text-muted">(χωρίς ΦΠΑ)</small></th>
                        <th>Σύνολο <small class="text-muted">(χωρίς ΦΠΑ)</small></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($materials as $m): ?>
                    <tr>
                        <td><?php echo $m['name']; ?></td>
                        <td><?php echo $m['quantity']; ?></td>
                        <td><?php echo formatCurrency($m['unit_price']); ?></td>
                        <td><?php echo formatCurrency($m['total']); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="3" class="text-right"><strong>Σύνολο Υλικών:</strong></td>
                        <td><strong><?php echo formatCurrencyWithVAT($materialTotal); ?></strong></td>
                    </tr>
                </tfoot>
            </table>
        </div>
        
        <div class="card-footer text-muted">
            <small>* Για την προσθήκη ΦΠΑ 24%, πολλαπλασιάστε επί 1.24</small>
        </div>
    </div>
</div>
```

---

## 🔧 **Helper για Admin**

Αν θέλεις να **ενεργοποιείς/απενεργοποιείς** τη σημείωση globally:

```php
// config/config.php
define('SHOW_VAT_NOTES', true);

// Στα views:
<?php if (SHOW_VAT_NOTES): ?>
    <small class="text-muted">(χωρίς ΦΠΑ)</small>
<?php endif; ?>
```

---

## ✅ **Summary**

1. ✅ Νέες helper functions στο config.php
2. ✅ Translations στο el.json
3. ⚠️ Εφάρμοσε σε όλα τα views (δες checklist)
4. ⚠️ Test σε production

**Εκτιμώμενος χρόνος:** 2-3 ώρες για όλα τα views

---

**Έτοιμος να ξεκινήσεις;** 🚀
