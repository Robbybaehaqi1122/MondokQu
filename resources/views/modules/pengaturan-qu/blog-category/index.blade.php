<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="page-title mb-0">Kategori Blog</h2>
            <a href="{{ route('pengaturan.blog-category.create') }}" class="btn btn-primary">
                <i class="ti ti-plus"></i> Tambah Kategori
            </a>
        </div>
    </x-slot>

    <div class="card">
        <div class="card-body">
            @if ($categories->isEmpty())
                <div class="empty">
                    <div class="empty-icon">
                        <i class="ti ti-category fs-1"></i>
                    </div>
                    <p class="empty-title">Belum ada kategori</p>
                    <p class="empty-subtitle text-secondary">Buat kategori untuk mengelompokkan blog.</p>
                    <a href="{{ route('pengaturan.blog-category.create') }}" class="btn btn-primary">
                        <i class="ti ti-plus"></i> Tambah Kategori
                    </a>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-vcenter">
                        <thead>
                            <tr>
                                <th>Nama</th>
                                <th>Slug</th>
                                <th>Deskripsi</th>
                                <th>Jumlah Blog</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($categories as $category)
                                <tr>
                                    <td class="fw-medium">{{ $category->name }}</td>
                                    <td><code>{{ $category->slug }}</code></td>
                                    <td class="text-secondary">{{ $category->description ?? '-' }}</td>
                                    <td>
                                        <span class="badge bg-secondary-lt">{{ $category->blogs_count }}</span>
                                    </td>
                                    <td>
                                        <div class="d-flex gap-1">
                                            <a href="{{ route('pengaturan.blog-category.edit', $category) }}" class="btn btn-sm btn-outline-secondary">
                                                <i class="ti ti-edit"></i>
                                            </a>
                                            <form action="{{ route('pengaturan.blog-category.destroy', $category) }}" method="POST" onsubmit="return confirm('Hapus kategori ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                                    <i class="ti ti-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    {{ $categories->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
