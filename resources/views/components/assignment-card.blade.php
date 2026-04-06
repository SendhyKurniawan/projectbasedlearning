@props(['assignment'])

<div class="group bg-surface-container-lowest p-6 rounded-2xl flex flex-col md:flex-row md:items-center gap-6 {{ $assignment->type === 'tugas' ? 'border-l-4 border-secondary' : 'border-l-4 border-tertiary' }} transition-all hover:bg-white hover:shadow-xl hover:shadow-primary/5 cursor-grab drag-handle relative sortable-item ring-1 ring-outline-variant/20" data-id="{{ $assignment->id }}">
    
    <div class="w-16 h-16 rounded-2xl bg-surface-container-low flex items-center justify-center {{ $assignment->type === 'tugas' ? 'text-primary' : 'text-tertiary' }} shrink-0">
        <span class="material-symbols-outlined text-3xl">
            {{ $assignment->type === 'tugas' ? 'architecture' : ($assignment->type === 'exercise' ? 'code' : 'quiz') }}
        </span>
    </div>
    
    <div class="flex-1 min-w-0">
        <div class="flex flex-wrap items-center gap-3 mb-2">
            <h4 class="text-[17px] font-bold text-on-background line-clamp-1 break-all tracking-tight">{{ $assignment->title }}</h4>
            
            <div class="flex items-center gap-2">
                @if($assignment->type === 'exercise')
                    <span class="px-2 py-0.5 bg-tertiary-fixed text-on-tertiary-fixed-variant text-[10px] font-black rounded uppercase tracking-tighter whitespace-nowrap">EXERCISE</span>
                @elseif($assignment->type === 'quiz')
                    <span class="px-2 py-0.5 bg-orange-100 text-orange-800 text-[10px] font-black rounded uppercase tracking-tighter whitespace-nowrap">QUIZ</span>
                @else
                    <span class="px-2 py-0.5 bg-secondary-container/30 text-on-secondary-container text-[10px] font-black rounded uppercase tracking-tighter whitespace-nowrap">TUGAS</span>
                @endif
    
                @if($assignment->deadline < now())
                    <span class="px-2 py-0.5 bg-surface-variant text-on-surface-variant text-[10px] font-black rounded uppercase tracking-tighter whitespace-nowrap">CLOSED</span>
                @else
                    <span class="px-2 py-0.5 bg-green-100 text-green-800 text-[10px] font-black rounded uppercase tracking-tighter whitespace-nowrap">ACTIVE</span>
                @endif
            </div>
        </div>
        
        <div class="flex flex-wrap items-center gap-x-6 gap-y-2">
            <div class="flex items-center gap-1.5 text-on-surface-variant text-xs font-medium">
                <span class="material-symbols-outlined text-[16px] opacity-70">calendar_month</span>
                {{ $assignment->deadline->format('d M y, H:i') }}
            </div>
            <div class="flex items-center gap-1.5 text-on-surface-variant text-xs font-medium">
                <span class="material-symbols-outlined text-[16px] opacity-70">military_tech</span>
                Max: {{ $assignment->max_score }}
            </div>
            <div class="flex items-center gap-1.5 justify-center px-2.5 py-0.5 bg-primary/5 rounded-md text-primary text-xs font-bold">
                <span class="material-symbols-outlined text-[16px]">group</span>
                {{ $assignment->submissions_count }} subs
            </div>
        </div>
    </div>
    
    <div class="flex flex-wrap md:flex-nowrap items-center gap-2 mt-4 md:mt-0 ml-auto shrink-0 w-full md:w-auto">
        @if($assignment->type === 'quiz')
            <a href="{{ route('dosen.assignments.questions.index', $assignment) }}" class="flex-1 md:flex-none flex justify-center items-center p-3 text-on-surface-variant bg-surface-container-high hover:bg-green-100 hover:text-green-700 rounded-xl transition-all" title="Kelola Pertanyaan">
                <span class="material-symbols-outlined text-[20px]">help_center</span>
            </a>
        @endif
        <a href="{{ route('dosen.assignments.edit', $assignment) }}" class="flex-1 md:flex-none flex justify-center items-center p-3 text-on-surface-variant bg-surface-container-high hover:bg-blue-100 hover:text-blue-700 rounded-xl transition-all" title="Edit">
            <span class="material-symbols-outlined text-[20px]">edit</span>
        </a>
        <a href="{{ route('dosen.assignments.submissions', $assignment) }}" class="flex-[3] md:flex-none flex justify-center items-center gap-2 px-5 py-2.5 bg-primary/10 text-primary font-bold text-sm rounded-xl hover:bg-primary hover:text-white transition-all shadow-sm group/btn">
            <span class="material-symbols-outlined text-[18px] group-hover/btn:scale-110 transition-transform">fact_check</span>
            Reviews
        </a>
        <form action="{{ route('dosen.assignments.destroy', $assignment) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus ini?')" class="m-0 p-0 flex-1 md:flex-none flex">
            @csrf
            @method('DELETE')
            <button type="submit" class="w-full flex justify-center items-center p-3 text-on-surface-variant bg-surface-container-high hover:bg-red-100 hover:text-red-700 rounded-xl transition-all" title="Hapus">
                <span class="material-symbols-outlined text-[20px]">delete</span>
            </button>
        </form>
    </div>
</div>
