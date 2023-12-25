import { Injectable } from '@angular/core';

const domain = 'https://result.school';

export enum ProductType {
  Skill = 'skill',
  Intensive = 'intensive',
  Course = 'course',
}

export interface IProduct {
  id: string;
  title: string;
  link: string;
  image: string;
  text: string;
  time: string;
  type: ProductType;
}

function addDomainToLinkAndImage(product: IProduct) {
  return {
    ...product,
    image: domain + product.image,
    link: domain + product.link,
  }
}

const products: IProduct[] = [
  {
    id: '29',
    title: 'TypeScript',
    link: '/products/typescript',
    image: '/img/icons/products/icon-ts.svg',
    text: 'Основы, типы, компилятор, классы, generic, утилиты, декораторы, advanced...',
    time: 'С опытом - 2 недели',
    type: ProductType.Skill,
  },
  {
    id: '30',
    title: 'Git и GitHub',
    link: '/products/git',
    image: '/img/icons/products/icon-git.svg',
    text: 'BLD, история версий, ветвление, удалённый репозиторий, релизы, opensourse...',
    time: 'С опытом - 2 недели',
    type: ProductType.Skill,
  },
  {
    id: '910',
    title: 'Redux, Redux Toolkit и MobX',
    link: '/products/state-managers',
    image: '/img/icons/products/icon-state-managers.svg',
    text: 'Redux, React Redux, Redux DevTools, Redux Toolkit, RTK Query, MobX...',
    time: 'С опытом - 2 недели',
    type: ProductType.Intensive,
  },
  {
    id: '940',
    title: 'React Advanced',
    link: '/products/react',
    image: '/img/icons/products/icon-react.svg',
    text: 'React JS, Хуки, Формы, React Route v6, Context API, Оптимизация, Архитектура, PWA...',
    time: 'С опытом - 8 недель',
    type: ProductType.Course,
  },
  {
    id: '920',
    title: 'Фронтенд-разработчик',
    link: '/products/first-stage',
    image: '/img/icons/products/icon-first-stage.svg',
    text: 'Javascript, Debug, DOM, Webpack, ES6 Import + Export, Git, GitFlow...',
    time: 'С нуля - 3 месяца',
    type: ProductType.Course,
  },
];

const initialState = {
  skill: [{}],
  intensive: [{}],
  course: [{}]
}

@Injectable({
  providedIn: 'root',
})
export class ProductsService {
  readonly products: IProduct[] = products.map(addDomainToLinkAndImage);

  constructor() { }

  getById(id: string) {
    return this.products.find(p => p.id === id);
  }

  get byGroup() {
    return this.products.reduce((group, prod) => {
      if (!group[prod.type]) {
        group[prod.type] = [];
      }
      if (JSON.stringify(group[prod.type][0]) == '{}') {
        group[prod.type][0] = prod;
      }else {
        group[prod.type].push(prod);
      }

      return group;
    }, initialState)
  }
}
