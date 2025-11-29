# Exercise 5 — Migrations no Yii2

Branch: `exercise-5`

## Objetivos

- Reduzir consultas ao banco e custo de hidratação na listagem de clientes e suas pessoas relacionadas.
- Adicionar cache com invalidação automática e fragment cache por seção, mantendo consistência.

## Checklist

- [x] Fase 1
  - [x] Migration de índices + teste functional.
  - [x] Paginação/filtro (repo/use case/controller/view) + testes unit/functional.
  - [x] Seed grande + functional.
- [ ] Fase 2
  - [ ] Consultas menores e menos hidratação (read-model).
  - [ ] Testes unit/functional (Read-model asArray + DTOs).
- [ ] Fase 3
  - [ ] Interface/adapter de cache + use case (FileCache) + unit/functional.
  - [ ] Behaviors de invalidação (Customer/Person/Pivot) + functional.
  - [ ] Fragment cache por seção (bloco de cliente) + functional.
- [ ] Fase 4 (opcional)
  - [ ] Trocar provider para MemCache (compose + config) + smoke.
  - [ ] Observabilidade/documentação + validação final.
