# Рекомендации по проекту

Универсальные соглашения для Laravel 13 + Filament 5 + `maksde/helpers`. Документ предназначен для переноса в новые проекты — не привязан к конкретному репозиторию.

## Содержание

- [Filament-ресурсы (Filament Resources)](#filament-ресурсы-filament-resources)
  - [Таблица (Table)](#таблица-table)
    - [Столбцы (columns)](#столбцы-columns)
    - [Действия над таблицей (toolbarActions)](#действия-над-таблицей-toolbaractions)
    - [Действия строки (recordActions)](#действия-строки-recordactions)
    - [Фильтры (filters)](#фильтры-filters)
  - [Форма (Form)](#форма-form)
    - [Builder контента (форма)](#builder-контента-форма)

- [Модель (Model)](#модель-model)

- [База данных (Database)](#база-данных-database)
  - [Миграция (Migration)](#миграция-migration)
    - [Создание таблицы (create_*)](#создание-таблицы-create_)
    - [Изменение таблицы (alter)](#изменение-таблицы-alter)

  - [Фабрика (Factory)](#фабрика-factory)
    - [Builder контента (фабрика)](#builder-контента-фабрика)
  - [Сидер (Seeder)](#сидер-seeder)

- [API](#api)
  - [Builder контента (API)](#builder-контента-api)

---

## Filament-ресурсы (Filament Resources)

Структура Filament 5: `*Resource.php`, `Tables/*Table.php`, `Schemas/*Form.php`. Метод `configure(Table|Schema $...)` возвращает настроенную таблицу или форму.

Поля форм, таблиц, infolist и фильтров — по возможности через компоненты плагина [`maksde/helpers`](https://github.com/demyanenkomaks/helpers) (документация: `vendor/maksde/helpers/documentation/`). **Стараться** вызывать поля для форм и таблиц с помощью этих компонентов при заполнении ресурсов Filament, если тип поля покрыт плагином. Нативные компоненты Filament — для всего остального (Select, FileUpload, Repeater, Builder и т.д.).

**Обязательные служебные поля:** `id`, `created_at`, `updated_at` (если модель использует timestamps).

**Опциональные служебные поля:** `sort_order` (справочники с ручной сортировкой), `is_active` (сущности с публикацией/видимостью). Если поля нет в модели — соответствующий столбец, фильтр или toggle **не добавляем**.

---

### Таблица (Table)

Класс `*Table.php`, метод `configure(Table $table): Table`. Ниже — правила по частям; **один** пример кода в конце раздела.

#### Столбцы (columns)

| Название | Поле | Компонент | Позиция | Описание |
| --- | --- | --- | --- | --- |
| Идентификатор | `id` | `IdColumn::make()` | первый | **Обязательно.** Пример: `IdColumn::make()` — всегда первый столбец |
| Порядок сортировки | `sort_order` | `TextColumn::make('sort_order')->numeric()->sortable()->toggleable(isToggledHiddenByDefault: true)` | после `id` | **Если есть в модели.** Скрыт по умолчанию; на таблице — `->defaultSort('sort_order')` и `->reorderable('sort_order')` |
| Активный | `is_active` | `BooleanIconColumn::make('is_active', 'Активный')` | после `id` или `sort_order` | **Если есть в модели.** Признак активности / публикации в списке |
| Даты создания и обновления | `created_at` + `updated_at` | `...CreateUpdateColumns::make()` | последние | **Обязательно** при timestamps. Хелпер + `...` — два столбца одной строкой, скрыты по умолчанию |
| Дата создания | `created_at` | `TimestampColumn::make('created_at', isToggledHiddenByDefault: true)` | предпоследний | **Явно двумя строками** — если нужна отдельная настройка `created_at` |
| Дата обновления | `updated_at` | `TimestampColumn::make('updated_at', isToggledHiddenByDefault: true)` | последний | **Явно двумя строками** — всегда после `created_at` |

---

#### Действия над таблицей (toolbarActions)

Кнопки над таблицей. Доменные bulk-действия — на List-странице в `getHeaderActions()`, не здесь.

| Название | Условие | Компонент | Позиция | Описание |
| --- | --- | --- | --- | --- |
| Создать | справочник с CRUD (`index` + `create` + `edit`) | `CreateAction::make()` | `toolbarActions` | **Filament:** `Filament\Actions\CreateAction`. Не maksde |
| — | `canCreate(): false` | без `CreateAction` | `toolbarActions` не задаём | Создание недоступно или выполняется вне ресурса |
| List-страница | справочник с CRUD | без `CreateAction` в `getHeaderActions()` | `Pages/List*.php` | Кнопку «Создать» **не** дублировать в шапке List |

---

#### Действия строки (recordActions)

Действия в строке. Компоненты из **maksde/helpers**; на edit-странице — `Filament\Actions\DeleteAction` в `getHeaderActions()`, не здесь.

| Название | Условие | Компонент | Позиция | Описание |
| --- | --- | --- | --- | --- |
| Редактировать | есть edit, запись редактируема | `EditAction::make()` | `recordActions`, первый | maksde. Скрыть через `->hidden(...)`, если действие недоступно для записи |
| Удалить | удаление разрешено | `DeleteAction::make()` | после `EditAction` | maksde. Не добавлять при `canDelete(): false` |
| Просмотр | только `view`, без edit | `ViewAction::make()` | `recordActions`, первый | maksde. Вместо `EditAction`, если редактирование недоступно |
| Удалить | просмотр + удаление | `DeleteAction::make()` | после `ViewAction` | maksde. Когда нужны оба действия в строке |

**Набор по умолчанию:** справочник CRUD — `EditAction` → `DeleteAction`. Иначе — только нужные actions; лишние не добавляем.

---

#### Фильтры (filters)

| Название | Условие | Компонент | Позиция | Описание |
| --- | --- | --- | --- | --- |
| Активный | есть `is_active` | `TernaryFilter::make('is_active')->label('Активный')` | первый | Выбор: все / активные / неактивные |
| Связи, enum, тип | по полям сущности | `SelectFilter::make(...)->relationship(...)` или `->options(...)` | после `is_active`, до дат | Пример: категория, тип записи |
| Даты создания и обновления | `created_at` + `updated_at` | `...CreateUpdateFilters::make()` | последние | Хелпер + `...`. Только даты — `CreateUpdateFilters::make()` без `...` |
| Дата создания | `created_at` | `TimestampFilter::make('created_at')` | предпоследний | **Явно двумя строками** |
| Дата обновления | `updated_at` | `TimestampFilter::make('updated_at')` | последний | **Явно двумя строками** — после `created_at` |

---

**Пример таблицы — подключения, столбцы, фильтры, actions и альтернативы в одном блоке:**

```php
<?php

declare(strict_types=1);

use Exception;
use Filament\Actions\CreateAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Maksde\Helpers\Filament\Resources\Tables\Actions\DeleteAction;
use Maksde\Helpers\Filament\Resources\Tables\Actions\EditAction;
use Maksde\Helpers\Filament\Resources\Tables\Actions\ViewAction;
use Maksde\Helpers\Filament\Resources\Tables\Columns\CreateUpdateColumns;
use Maksde\Helpers\Filament\Resources\Tables\Columns\IdColumn;
use Maksde\Helpers\Filament\Resources\Tables\Filters\CreateUpdateFilters;
// sort_order / is_active / SelectFilter — раскомментировать use при добавлении полей:
// use Maksde\Helpers\Filament\Resources\Tables\Columns\BooleanIconColumn;
// use Filament\Tables\Filters\TernaryFilter;
// use Filament\Tables\Filters\SelectFilter;
// Столбцы — явно двумя строками вместо ...CreateUpdateColumns::make():
// use Maksde\Helpers\Filament\Resources\Tables\Columns\TimestampColumn;
// Фильтры — явно двумя строками вместо ...CreateUpdateFilters::make():
// use Maksde\Helpers\Filament\Resources\Tables\Filters\TimestampFilter;

class ExampleTable
{
    /** @throws Exception */
    public static function configure(Table $table): Table
    {
        return $table
            // --- Столбцы ---
            ->columns([
                IdColumn::make(), // обязательно, первый

                // sort_order — только если есть в модели:
                // TextColumn::make('sort_order')
                //     ->label('Порядок')
                //     ->numeric()
                //     ->sortable()
                //     ->toggleable(isToggledHiddenByDefault: true),

                // is_active — только если есть в модели:
                // BooleanIconColumn::make('is_active', 'Активный'),

                TextColumn::make('name')
                    ->label('Название')
                    ->searchable()
                    ->sortable(),

                ...CreateUpdateColumns::make(),
                // TimestampColumn::make('created_at', isToggledHiddenByDefault: true),
                // TimestampColumn::make('updated_at', isToggledHiddenByDefault: true),
            ])
            // ->defaultSort('sort_order')   // только при sort_order
            // ->reorderable('sort_order')   // только при sort_order

            // --- Фильтры ---
            // Справочник только с датами: return $table->filters(CreateUpdateFilters::make());
            ->filters([
                // TernaryFilter::make('is_active')->label('Активный'), // если есть is_active
                ...CreateUpdateFilters::make(),
                // SelectFilter::make('category_id') ... — по полям сущности
                // TimestampFilter::make('created_at'),
                // TimestampFilter::make('updated_at'),
            ])

            // --- recordActions (maksde) — справочник CRUD по умолчанию ---
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            // Только просмотр: ->recordActions([ViewAction::make()])
            // Просмотр + удаление: ->recordActions([ViewAction::make(), DeleteAction::make()])
            // Условное скрытие: EditAction::make()->hidden($condition)

            // --- toolbarActions — CreateAction только здесь, не в List*.php ---
            ->toolbarActions([
                CreateAction::make(), // справочник CRUD
                // canCreate(): false — toolbarActions не задаём
            ]);
    }
}

// List-страница справочника — getHeaderActions() не переопределяем (без CreateAction).
// Доменные bulk-действия — в List*.php → getHeaderActions(), не в toolbarActions.
// Edit-страница — DeleteAction::make() (Filament) в getHeaderActions().
```

---

### Форма (Form)

Класс `*Form.php`, метод `configure(Schema $schema): Schema`.

| Название | Поле | Компонент | Позиция | Описание |
| --- | --- | --- | --- | --- |
| Даты создания и обновления | `created_at` + `updated_at` | `...CreateUpdateTextEntry::make()` | первые | Только чтение на edit/view. Одной строкой через хелпер + `...` |
| Дата создания | `created_at` | `TimestampTextEntry::make('created_at')` | первый | **Явно двумя строками** |
| Дата обновления | `updated_at` | `TimestampTextEntry::make('updated_at')` | после `created_at` | **Явно двумя строками** |
| Активный | `is_active` | `BooleanToggleForm::make('is_active', 'Активный')` | после timestamps | **Если есть в модели.** Toggle публикации |
| Другой boolean | по полю модели | `BooleanToggleForm::make(...)` | на одной строке с `is_active` | **Если есть.** Несколько boolean — `columns(2)` на Schema |
| Уникальное поле | справочник, значение **не** приходит из внешнего сервиса | `TextInput::...->unique(ignoreRecord: true)` | на поле ввода | **Обязательно** вместе с `->unique()` на той же колонке в миграции `create_*`. Для записей с `external_id` из импорта — уникальность по `external_id`, не по `name` |
| Slug | есть поле `slug` | `...->unique(ignoreRecord: true)` | на поле ввода | **Обязательно** вместе с `->unique()` на колонке `slug` в миграции `create_*` |

---

**Пример формы — подключения и альтернативы в одном блоке:**

```php
<?php

declare(strict_types=1);

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Maksde\Helpers\Filament\Resources\Schemas\Forms\BooleanToggleForm;
use Maksde\Helpers\Filament\Resources\Schemas\Infolists\CreateUpdateTextEntry;
// Явно двумя строками — раскомментировать вместо ...CreateUpdateTextEntry::make():
// use Maksde\Helpers\Filament\Resources\Schemas\Infolists\TimestampTextEntry;

class ExampleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            ...CreateUpdateTextEntry::make(),
            // TimestampTextEntry::make('created_at'),
            // TimestampTextEntry::make('updated_at'),

            BooleanToggleForm::make('is_active', 'Активный'), // убрать, если поля нет
            BooleanToggleForm::make('is_featured', 'Избранное'), // убрать, если поля нет

            TextInput::make('name')
                ->label('Название')
                ->required()
                ->maxLength(255)
                ->unique(ignoreRecord: true), // справочник без external_id из внешнего сервиса
            Select::make('category_id')
                ->label('Категория')
                ->relationship('category', 'name')
                ->searchable()
                ->preload(),
        ]);
    }
}
```

---

#### Builder контента (форма)

JSON-поле (например `content`) на моделях с блочным контентом — массив блоков Filament Builder. Каждый блок: `{ "type": "<ключ>", "data": { ... } }`. Перечень `type` и полей — **в документации домена конкретного проекта**, не здесь. Один `type` и одни ключи `data` должны совпадать в [форме](#builder-контента-форма), [фабрике](#builder-контента-фабрика) и [API](#builder-контента-api) (если контракт API не оговорён отдельно в продуктовой спецификации).

**Два допустимых варианта организации формы:**

| Вариант | Расположение | Когда |
| --- | --- | --- |
| **А. Агрегатор** | `Modules/{Module}/app/Filament/Support/{Entity}ContentBuilder.php`, метод `make(): Builder`; блоки — private static-методы | Несколько блоков одной сущности, общие private-хелперы для полей |
| **Б. Отдельные классы** | `Modules/{Module}/app/Filament/Components/Forms/Builder/{Name}Block.php`, метод `make(): Block` | Крупный каталог блоков, переиспользование блока в разных Builder |

Подключение: `{Entity}ContentBuilder::make()` или `Builder::make('<поле>')->blocks([...])` в `*Form.php`.

| Правило | Описание |
| --- | --- |
| Класс | `Block::make('<type>')` — `type` в **snake_case** |
| Поля | Имена в `->schema([...])` = ключи в `data` JSON (см. [фабрику](#builder-контента-фабрика)) |
| Хелперы | Строки — `StringCharCount`; длинный текст — `TextCharCount`; boolean — `BooleanToggleForm`; файлы — `FileUpload` (+ `->image()` при необходимости) |
| Вложенный Builder | Родительский блок — `Builder::make('<ключ>')->blocks([...])`; имя Builder = ключ массива вложенных блоков в `data` |
| Repeater | Массив в `data` — `Repeater::make('<ключ>')` с **тем же** именем |
| Подключение | Блок в `->blocks([...])` только тех форм, где нужен |

Данные сохраняются в БД как JSON и отдаются фронту через [API](#builder-контента-api) (`{Name}BlockResource`); тестовые значения — в [фабрике](#builder-контента-фабрика).

**Пример** — поля `schema` = ключи `data` (иллюстрация, не каталог блоков):

```php
Block::make('example_block')->schema([
    StringCharCount::make('title', 'Заголовок'),           // → data.title
    TextCharCount::make('body', 'Текст', columnSpan: 'full'), // → data.body
    BooleanToggleForm::make('is_visible', 'Видимый'),      // → data.is_visible
]);
```

---

## Модель (Model)

Eloquent-модель: PHPDoc, mass assignment, casts, связи.

| Название | Поле/Условие | Правило | Описание |
| --- | --- | --- | --- |
| Описание класса | — | PHPDoc блока класса | Кратко на русском, что за сущность |
| Атрибуты | все колонки модели | `@property` / `@property-read` | Русские подписи; `@property-read` — для связей и вычисляемых свойств |
| Mass assignment | `$fillable` | **Обязательно.** | Только поля для `create`, `update`, `fill`. Без `$guarded = []`. Не включать `id`, timestamps |
| JSON-ключи | `options->enabled` и т.п. | `#[Fillable(['column->key'])]` | Вложенные ключи JSON — только через `Fillable`, не через `Guarded` |
| Приведение типов | поля с нестандартным типом в PHP | `casts(): array` | Метод `casts()` (не `$casts`). Типы: `integer`, `float`, `double`, `decimal:<precision>`, `string`, `boolean`, `array`, `collection`, `object`, `json`, `date`, `datetime`, `immutable_date`, `immutable_datetime`, `timestamp`, `hashed`, `encrypted` (+ `:array`, `:collection`, `:object`), enum-классы, `AsEnumCollection`, `AsFluent`, `AsStringable`, `AsUri` — по необходимости |
| Фабрика | есть `{Model}Factory` | `HasFactory` + `@use HasFactory<XFactory>` + `newFactory()` | **Только если фабрика создана.** Без фабрики — trait и `newFactory()` не добавляем |
| Связи | BelongsToMany, HasMany, BelongsTo, Morph* и т.д. | return type + `@return Relation<RelatedModel, $this>` | Нативный return type **обязателен**. Generic PHPDoc **обязателен**|

---

**`$fillable` — дополнительно (Laravel):**

- Eloquent по умолчанию защищён от mass assignment; без `Fillable` / `$fillable` метод `Model::create([...])` не заполнит поля.
- Поля вне whitelist при mass assignment **молча отбрасываются**; в dev можно включить `Model::preventSilentlyDiscardingAttributes()` — тогда будет исключение.
- `$hidden` — отдельно, для сериализации (пароли, tokens), не путать с `$fillable`.

---

**Пример-каркас (справочник с фабрикой и связью):**

```php
<?php

namespace App\Models;

use Database\Factories\TagFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Carbon;

/**
 * Тег.
 *
 * @property int $id Идентификатор
 * @property Carbon|null $created_at Добавлен
 * @property Carbon|null $updated_at Отредактирован
 * @property string $name Название
 * @property int $sort_order Порядок сортировки
 * @property-read Collection<int, Article> $articles Связанные записи
 */
class Tag extends Model
{
    /** @use HasFactory<TagFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'sort_order',
    ];

    /**
     * @return BelongsToMany<Article, $this>
     */
    public function articles(): BelongsToMany
    {
        return $this->belongsToMany(Article::class)
            ->withPivot('sort_order')
            ->orderByPivot('sort_order');
    }

    protected static function newFactory(): TagFactory
    {
        return TagFactory::new();
    }
}
```

---

**Пример `casts()` (фрагмент):**

```php
protected function casts(): array
{
    return [
        'is_active' => 'boolean',
        'content' => 'array',
        'published_at' => 'datetime',
        'price' => 'decimal:2',
    ];
}
```

---

## База данных (Database)

Миграции, фабрики, сидеры.

---

### Миграция (Migration)

#### Создание таблицы (create_*)

Первая миграция сущности. Имя файла: `YYYY_MM_DD_HHMMSS_create_{table}_table.php`.

| Название | Правило | Описание |
| --- | --- | --- |
| Порядок полей | `id()` → `timestamps()` → остальные колонки | Служебные поля — в начале, как в примере ниже |
| Справочник без внешнего ключа | `->unique()` на естественном ключе | Например `name`, если значение вводится в админке, а не приходит из стороннего сервиса. В форме — `->unique(ignoreRecord: true)` на том же поле |
| Slug | есть поле `slug` | `$table->string('slug')->unique()` | на колонке `slug` | **Обязательно.** В форме — `->unique(ignoreRecord: true)` на том же поле |
| Справочник из внешнего сервиса | `external_id` (или аналог) `->unique()` | Upsert по `external_id`; `name` может быть без unique |
| Порядок сортировки | `sort_order` | `unsignedInteger()->default(999)` — если нужна ручная сортировка в Filament |
| Откат | `Schema::dropIfExists('{table}')` | В `down()` |

---

**Пример create-миграции (справочник):**

```php
<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tags', function (Blueprint $table): void {
            $table->id();
            $table->timestamps();

            $table->string('name')->unique();
            $table->unsignedInteger('sort_order')->default(999);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tags');
    }
};
```

---

#### Изменение таблицы (alter)

**Два обязательных действия на каждое изменение схемы:**

1. **Обновить исходную миграцию `create_*`** — поле добавить, изменить или убрать там, где таблица создаётся впервые. Так `migrate:fresh` / новые окружения получают актуальную схему.
2. **Отдельная alter-миграция** — для уже развёрнутых БД; в `up()` и `down()` **проверять существование** таблицы/колонки.

| Операция | Проверка в `up()` | Проверка в `down()` |
| --- | --- | --- |
| Добавить колонку | `Schema::hasTable(...)` и `! Schema::hasColumn(..., 'field')` | `Schema::hasColumn(..., 'field')` перед `dropColumn` |
| Изменить колонку | `Schema::hasColumn(..., 'field')` перед `->change()` | откат с той же проверкой |
| Удалить колонку | `Schema::hasColumn(..., 'field')` перед `dropColumn` | `! Schema::hasColumn(..., 'field')` перед повторным добавлением |

---

**Пример alter-миграции:**

```php
public function up(): void
{
    if (! Schema::hasTable('tags') || Schema::hasColumn('tags', 'slug')) {
        return;
    }

    Schema::table('tags', function (Blueprint $table): void {
        $table->string('slug')->nullable()->after('name');
    });
}

public function down(): void
{
    if (! Schema::hasTable('tags') || ! Schema::hasColumn('tags', 'slug')) {
        return;
    }

    Schema::table('tags', function (Blueprint $table): void {
        $table->dropColumn('slug');
    });
}
```

**Не делать:** alter только в отдельной миграции без правки `create_*` — схема разъедется между fresh-install и prod.

---

### Фабрика (Factory)

| Название | Условие | Правило | Описание |
| --- | --- | --- | --- |
| Класс | модель с тестами или сидером | `{Model}Factory` | Namespace `Database\Factories` |
| `$model` | обязательно | `protected $model = Model::class` | Связь фабрики с моделью |
| `definition()` | обязательно | поля из `$fillable` | Генерация через `fake()` |
| Связи | FK | `RelatedModel::factory()` | Вместо raw id |
| Общие атрибуты | сложная сущность | `showcaseAttributes(array $overrides = [])` | Статический метод для переиспользования в сидере и тестах |

---

**Пример:**

```php
<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Tag;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Tag>
 */
class TagFactory extends Factory
{
    protected $model = Tag::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->words(2, true),
            'sort_order' => fake()->numberBetween(1, 100),
        ];
    }
}
```

States (`->state(...)`) — по необходимости, не обязательный шаблон.

---

#### Builder контента (фабрика)

Структура — зеркало полей [формы](#builder-контента-форма); отдаётся в API через [transformers](#builder-контента-api). Каталог `type` и полей — в документации домена проекта.

**Два допустимых варианта:**

| Вариант | Расположение | Когда |
| --- | --- | --- |
| **А. Метод фабрики** | `{Model}Factory::builderContent(): array`; в `definition()` / `showcaseAttributes`: `'content' => self::builderContent()` | Компактно для сидов и тестов |
| **Б. Отдельные классы** | `Modules/{Module}/database/factories/Builder/{Name}Block.php`, метод `make(): array`; агрегатор собирает массив | Много блоков, переиспользование в нескольких фабриках |

| Правило | Описание |
| --- | --- |
| Формат | `['type' => '<type>', 'data' => [ ... ]]` — `type` в **snake_case** |
| Ключи `data` | Те же имена, что поля в `schema` формы; значения — валидные для JSON |
| Вложенность | Родительский блок: массив вложенных `{ type, data }` в `data.<ключ>` — тот же ключ, что у вложенного `Builder::make('<ключ>')` в форме |
| Верхний уровень | Новый блок — добавить в `builderContent()` или в агрегатор |
| Файлы | Строковые пути-заглушки, не бинарные данные |

**Пример** — те же ключи, что в [форме](#builder-контента-форма):

```php
return [
    'type' => 'example_block',
    'data' => [
        'title' => fake()->sentence(3),
        'body' => fake()->paragraph(),
        'is_visible' => fake()->boolean(80),
    ],
];
```

При добавлении блока: [форма](#builder-контента-форма) → [фабрика](#builder-контента-фабрика) → [API](#builder-контента-api) — `type` и ключи `data` согласованы во всех слоях.

---

### Сидер (Seeder)

| Тип | Когда | Метод upsert | Ключ |
| --- | --- | --- | --- |
| Product-справочник | фиксированный набор для админки | `firstOrCreate` | `name` |
| Справочник с составным ключом | несколько полей уникальности | `updateOrCreate` | составной ключ |
| Справочник с внешним ключом | есть `external_id` | `updateOrCreate` | `external_id` |
| Демо-данные | связанные сущности для dev/QA | фабрики / private-методы | — |
| Корневой сидер | порядок зависимостей | `$this->call([...])` | справочники → зависимые → демо |

---

**Product-справочник:**

```php
$items = [
    ['name' => 'Первый', 'sort_order' => 1],
    ['name' => 'Второй', 'sort_order' => 2],
];

foreach ($items as $item) {
    Tag::query()->firstOrCreate(['name' => $item['name']], $item);
}
```

---

**Справочник с `external_id`:**

```php
foreach ($items as $item) {
    Example::query()->updateOrCreate(
        ['external_id' => $item['external_id']],
        $item,
    );
}
```

---

**Корневой сидер:** порядок — от независимых справочников к сущностям с FK; демо-сидеры — в конце.

```php
$this->call([
    ReferenceSeeder::class,
    TagSeeder::class,
    DependentSeeder::class, // после зависимостей
]);
```

---

## API

REST API вынесен в отдельный модуль `Modules/{Module}` (создание: `php artisan module:make {Module}`). `{Module}` — имя API-модуля в StudlyCase. Все новые классы API — через artisan-команды пакета [`coolsam/modules`](https://github.com/savannabits/filament-modules) ([документация команд](https://laravelmodules.com/docs/12/advanced/artisan-commands)), а не вручную в `app/` или в модулях с доменными моделями.

Важно: команда `module:make-resource` создаёт классы в `app/Transformers/`, а не в `Http/Resources` — в проекте API-ответы называются **Transformers**.

**Общий принцип:** структура каталогов, namespace и суффиксы имён классов должны совпадать с тем, что выдаёт генератор. Arch-тесты (`composer test:arch`) это проверяют.

---

**Дополнительно:**

- PSR-4 модуля: `Modules\{Module}\` → `app/` (см. `Modules/{Module}/composer.json`).
- Transformers наследуют `Illuminate\Http\Resources\Json\JsonResource`, метод `toArray(Request $request)`.
- Контроллеры наследуют `App\Http\Controllers\Controller`.
- Модели и бизнес-данные — из доменного модуля, в API-модуле не дублировать.

---

### Маршруты

- Регистрация только в `Modules/{Module}/routes/api.php`.
- Префикс API (например `api/v1`) задаётся в `RouteServiceProvider` модуля — в файле маршрутов не дублировать.
- Группировка: `Route::prefix(...)->controller(...)->group(...)`.

---

### Подпапки transformers

- Вложенные transformers (`Transformers/{Entity}/`, `Transformers/Builder/`) — допустимы для группировки.
- Класс по-прежнему с суффиксом `Resource`, extends `JsonResource`.
- Namespace отражает подпапку (например `Modules\{Module}\Transformers\Builder\Blocks\...`).

---

### Builder контента (API)

Блочный JSON из БД преобразуется для фронта через transformers. **Каталог блоков и отличия контракта API от JSON админки** — в документации домена проекта. По умолчанию ключи `data` совпадают с [формой](#builder-контента-форма) и [фабрикой](#builder-контента-фабрика); API меняет только **формат** (`urlFront()` для путей к файлам, `(bool)` для флагов, `null` для пустых значений), **не переименовывает** поля.

Классы: `Modules/{Module}/app/Transformers/Builder/Blocks/{Name}BlockResource.php`, метод `toArray(Request $request): array`.

**Роутинг по `type`:**

| Уровень | Класс / метод | Назначение |
| --- | --- | --- |
| Корневой массив | `BuilderResource::match($type)` | Блоки верхнего уровня JSON-поля (`content` и т.п.) |
| Вложенный массив | `{Parent}BlockResource::matchNested($type)` | Блоки внутри `data.<ключ>` родителя с вложенным Builder |

| Правило | Описание |
| --- | --- |
| Вход | Массив блока из JSON (`$this['type']`, `$this['data']`) |
| Выход | `{ type, data }`; `type` без изменений |
| Преобразования | `urlFront()` для путей к файлам, `(bool)` для флагов |
| Repeater / массивы | Отдельный `*ItemResource` при нетривиальном маппинге элементов |
| Вызов | `BuilderResource::toCollectionArray($blocks, $request)` из transformer сущности |
| Несколько `type` | Один `*BlockResource` допустим, если структура `data` одинакова |

**Пример** — те же ключи, что в [форме](#builder-контента-форма) и [фабрике](#builder-контента-фабрика):

```php
return [
    'type' => $this['type'],
    'data' => [
        'title' => $data['title'] ?? null,
        'body' => $data['body'] ?? null,
        'is_visible' => (bool) ($data['is_visible'] ?? false),
    ],
];
```
