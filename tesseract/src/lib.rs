extern crate wee_alloc;

// Use `wee_alloc` as the global allocator.
#[cfg(feature="wee")]
#[global_allocator]
static ALLOC: wee_alloc::WeeAlloc = wee_alloc::WeeAlloc::INIT;

#[no_mangle]
fn add_one(x: i32) -> i32 {
    x + 1
}

#[no_mangle]
pub fn sum_all(path: Vec<i32>) -> i32 {
    path.iter().sum()
}

#[no_mangle]
pub fn array_length(path: Vec<i32>) -> i32 {
    path.len() as i32
}

#[repr(C)]
pub struct Path {
    from: i32,
    to: i32,
    cost: i32,
}

use std::alloc::{alloc, dealloc, Layout};
use std::mem;
use std::ptr::null_mut;

#[no_mangle]
pub unsafe extern "C" fn __free(ptr: *mut u8, size: usize) {
    // This happens for zero-length slices, and in that case `ptr` is
    // likely bogus so don't actually send this to the system allocator
    if size == 0 {
        return
    }
    let align = mem::align_of::<usize>();
    let layout = Layout::from_size_align_unchecked(size, align);
    dealloc(ptr, layout);
}

#[no_mangle]
pub extern "C" fn __malloc(size: usize) -> *mut u8 {
    let align = mem::align_of::<usize>();
    if let Ok(layout) = Layout::from_size_align(size, align) {
        unsafe {
            if layout.size() > 0 {
                let ptr = alloc(layout);
                if !ptr.is_null() {
                    return ptr
                }
            } else {
                return align as *mut u8
            }
        }
    }

    return null_mut()
}
