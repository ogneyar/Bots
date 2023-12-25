import { Component, inject } from '@angular/core';
import { TelegramService } from '../../services/telegram.service';
import { ProductsService } from '../../services/products.service';
import { ProductListComponent } from '../../components/product-list/product-list.component';

@Component({
  selector: 'app-shop',
  standalone: true,
  imports: [ProductListComponent],
  template: `
    <!-- <app-product-list 
      title="Отдельный навык"
      subtitle="Изучите востребованные технологии"
      [products]="products.byGroup.skill"
    />
    <app-product-list 
      title="Инртенсивы"
      subtitle="Экспресс программы"
      [products]="products['intensive']"
    />
    <app-product-list 
      title="Бесплатные курсы"
      subtitle="Необходимые навыки и проекты"
      [products]="products.products['course']"
    /> -->
  `,      //  ХЗ как тут сделать...
})
export class ShopComponent {
  telegram = inject(TelegramService);
  products = inject(ProductsService);

  constructor() {
    this.telegram?.MainButton?.show();
    // console.log(this.products.byGroup);
    // console.log(this.products);
  }
}
