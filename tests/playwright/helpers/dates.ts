function pad(n: number): string {
  return String(n).padStart(2, '0');
}

function formatDatetimeLocal(d: Date): string {
  return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}T${pad(d.getHours())}:${pad(d.getMinutes())}`;
}

export function futureDeadline(days = 14): string {
  return formatDatetimeLocal(new Date(Date.now() + days * 24 * 3600 * 1000));
}

export function pastDeadline(): string {
  return formatDatetimeLocal(new Date(Date.now() - 24 * 3600 * 1000));
}
