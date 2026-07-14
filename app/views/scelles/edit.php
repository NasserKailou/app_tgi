<?php $pageTitle = 'Modifier scellé'; ?>
<div class="d-flex align-items-center mb-4 mt-2">
    <a href="<?=BASE_URL?>/scelles/show/<?=$scelle['id']?>" class="btn btn-outline-secondary btn-sm me-3"><i class="bi bi-arrow-left"></i></a>
    <h4 class="fw-bold mb-0"><i class="bi bi-pencil me-2 text-primary"></i>Modifier le scellé</h4>
</div>
<div class="card border-0 shadow-sm" style="max-width:800px;margin:auto">
    <div class="card-body p-4">
        <form method="POST" action="<?=BASE_URL?>/scelles/update/<?=$scelle['id']?>">
            <?=CSRF::field()?>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Catégorie</label>
                    <select name="categorie" class="form-select" id="editCategorieScelle"
                            onchange="toggleEditCategorieAutre(this.value)">
                        <?php foreach(['arme'=>'Arme','drogue'=>'Drogue / Stupéfiant','document'=>'Document','argent'=>'Argent / Valeur','electronique'=>'Appareil électronique','vehicule'=>'Véhicule','autre'=>'Autre (à préciser)'] as $v=>$l): ?>
                        <option value="<?=$v?>" <?=$scelle['categorie']===$v?'selected':''?>><?=$l?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6" id="editRowCategorieAutre"
                     style="display:<?=$scelle['categorie']==='autre'?'block':'none'?>">
                    <label class="form-label fw-semibold">Préciser la catégorie</label>
                    <input type="text" name="categorie_autre_detail" class="form-control"
                           placeholder="Ex: Bijoux, Produits chimiques…"
                           value="<?=htmlspecialchars($scelle['categorie_autre_detail']??'')?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Lieu de conservation</label>
                    <input type="text" name="lieu_conservation" class="form-control" value="<?=htmlspecialchars($scelle['lieu_conservation']??'')?>">
                </div>
                <div class="col-12">
                    <label class="form-label fw-semibold">Description</label>
                    <textarea name="description" class="form-control" rows="4"><?=htmlspecialchars($scelle['description']??'')?></textarea>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Statut</label>
                    <select name="statut" class="form-select">
                        <?php foreach(['depose'=>'Déposé','inventorie'=>'Inventorié','restitue'=>'Restitué','detruit'=>'Détruit','confisque'=>'Confisqué'] as $v=>$l): ?>
                        <option value="<?=$v?>" <?=$scelle['statut']===$v?'selected':''?>><?=$l?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="d-flex gap-2 mt-4">
                <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Mettre à jour</button>
                <a href="<?=BASE_URL?>/scelles/show/<?=$scelle['id']?>" class="btn btn-outline-secondary">Annuler</a>
            </div>
        </form>
    </div>
</div>
<script>
function toggleEditCategorieAutre(val) {
    var el = document.getElementById('editRowCategorieAutre');
    if (el) el.style.display = (val === 'autre') ? 'block' : 'none';
}
</script>
